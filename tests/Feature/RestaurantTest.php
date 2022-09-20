<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        Sanctum::actingAs($this->user, ['*']);
    }

    // ...

    public function testAddRestaurantValidationWorks()
    {
        $this->postJson('/api/restaurants/add')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'address_line_1', 'pincode', 'city']);
    }

    public function testARestaurantCanBeAdded()
    {
        $this->postJson('/api/restaurants/add', [
            'name' => 'Test',
            'address_line_1' => 'Test line',
            'pincode' => '12345',
            'city' => 'Test',
        ])->assertOk()
            ->assertJsonStructure(['restaurant_id']);

        $this->assertDatabaseHas('restaurants', [
            'user_id' => $this->user->id,
            'name' => 'Test',
        ]);
    }


    public function testUpdateRestaurantValidationWorks()
    {
        $restaurant = Restaurant::factory()->create();

        $this->postJson('/api/restaurants/update/' . $restaurant->id)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'address_line_1', 'pincode', 'city']);
    }

    public function testARestaurantCanBeUpdated()
    {
        $restaurant = Restaurant::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->postJson('/api/restaurants/update/' . $restaurant->id, [
            'name' => 'New name',
            'address_line_1' => $restaurant->address_line_1,
            'pincode' => $restaurant->pincode,
            'city' => $restaurant->city,
        ])->assertOk();

        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'name' => 'New name',
        ]);
    }
    

    public function testOtherUserCannotUpdateARestaurant()
    {
        $restaurant = Restaurant::factory()->create();

        $this->postJson('/api/restaurants/update/' . $restaurant->id, [
            'name' => 'New name',
            'address_line_1' => $restaurant->address_line_1,
            'pincode' => $restaurant->pincode,
            'city' => $restaurant->city,
        ])->assertStatus(404);

        $this->assertDatabaseHas('restaurants', [
            'id' => $restaurant->id,
            'name' => $restaurant->name,
        ]);
    }
    
    public function testARestaurantCanBeArchived()
    {
        $restaurant = Restaurant::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->delete('/api/restaurants/archive/' . $restaurant->id)
            ->assertOk();

        $this->assertSoftDeleted($restaurant);
    }


    public function testOtherUserCannotArchiveARestaurant()
    {
        $restaurant = Restaurant::factory()->create();

        $this->delete('/api/restaurants/archive/' . $restaurant->id)
            ->assertStatus(404);
    }

}
