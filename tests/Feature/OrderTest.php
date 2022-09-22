<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class OrderTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $restaurant;

    private $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->restaurant = Restaurant::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->table = Table::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        Sanctum::actingAs($this->user, ['*']);
    }

    public function testRestaurantMustMatchBeforeBookingATable()
    {
        $table = Table::factory()->create();

        $this->postJson('/api/orders/book-a-table/' . $table->id, [
            'restaurant_id' => $this->restaurant->id,
        ])->assertStatus(404);
    }

    public function testATableCanBeBooked()
    {
        $this->postJson('/api/orders/book-a-table/' . $this->table->id, [
            'restaurant_id' => $this->restaurant->id,
        ])->assertOk()
            ->assertJsonStructure(['order_id']);

        $this->assertDatabaseHas('orders', [
            'restaurant_id' => $this->restaurant->id,
            'table_id' => $this->table->id,
        ]);

        $this->assertDatabaseHas('tables', [
            'id' => $this->table->id,
            'state' => 'App\ModelStates\Table\Occupied',
        ]);
    }
}
