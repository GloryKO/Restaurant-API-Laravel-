<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use App\ModelStates\Table\NonOperational;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class TableStatusTest extends TestCase
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

    public function testANewTableIsAvailableByDefault()
    {
        $this->postJson('/api/tables/add', [
            'restaurant_id' => $this->restaurant->id,
            'number' => 1,
        ])->assertOk()
            ->assertJsonStructure(['table_id']);

        $this->assertDatabaseHas('tables', [
            'restaurant_id' => $this->restaurant->id,
            'state' => 'App\ModelStates\Table\Available',
        ]);
    }

    public function testATableCanBeMarkedAsNonOperational()
    {
        $this->postJson('/api/tables/mark-as-non-operational/' . $this->table->id, [
            'restaurant_id' => $this->restaurant->id,
        ])->assertOk();

        $this->assertDatabaseHas('tables', [
            'id' => $this->table->id,
            'state' => 'App\ModelStates\Table\NonOperational',
        ]);
    }

    public function testATableCanBeMarkedAsAvailable()
    {
        $this->table->changeStatusTo(NonOperational::class);

        $this->postJson('/api/tables/mark-as-available/' . $this->table->id, [
            'restaurant_id' => $this->restaurant->id,
        ])->assertOk();

        $this->assertDatabaseHas('tables', [
            'id' => $this->table->id,
            'state' => 'App\ModelStates\Table\Available',
        ]);
    }

    public function testATableCanBeMarkedAsReserved()
    {
        $this->postJson('/api/tables/mark-as-reserved/' . $this->table->id, [
            'restaurant_id' => $this->restaurant->id,
        ])->assertOk();

        $this->assertDatabaseHas('tables', [
            'id' => $this->table->id,
            'state' => 'App\ModelStates\Table\Reserved',
        ]);
    }
}
