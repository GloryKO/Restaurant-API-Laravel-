<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class MenuTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $restaurant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->restaurant = Restaurant::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Sanctum::actingAs($this->user, ['*']);
    }

    public function testRestaurantSpecificationRequired()
    {
        $this->get('/api/menu-items/list')
            ->assertStatus(417);
    }

    public function testRestaurantMustBeOwnedByUser()
    {
        $restaurant = Restaurant::factory()->create();

        $this->json('GET', '/api/menu-items/list', [
            'restaurant_id' => $restaurant->id,
        ])
            ->assertStatus(404);
    }

    public function testMenuItemsCanBeFetched()
    {
        MenuItem::factory()->count(3)->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        $this->json('GET', '/api/menu-items/list', [
            'restaurant_id' => $this->restaurant->id,
        ])
            ->assertJson(fn (AssertableJson $json) => $json->has('menu_items', 3));
    }

    public function testOtherRestaurantMenuItemsArentFetched()
    {
        $restaurant = Restaurant::factory()->create([
            'user_id' => $this->user->id,
        ]);

        MenuItem::factory()->create([
            'restaurant_id' => $restaurant->id,
        ]);
        MenuItem::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        $this->json('GET', '/api/menu-items/list', [
            'restaurant_id' => $this->restaurant->id,
        ])
            ->assertJson(fn (AssertableJson $json) => $json->has('menu_items', 1));
    }

    public function testOtherUserMenuItemsArentFetched()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::factory()->create([
            'user_id' => $user,
        ]);

        MenuItem::factory()->create([
            'restaurant_id' => $restaurant->id,
        ]);
        MenuItem::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        $this->json('GET', '/api/menu-items/list', [
            'restaurant_id' => $this->restaurant->id,
        ])
            ->assertJson(fn (AssertableJson $json) => $json->has('menu_items', 1));
    }

    public function testAddMenuItemValidationWorks()
    {
        $this->postJson('/api/menu-items/add', [
            'restaurant_id' => $this->restaurant->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }

    public function testSameNameNotAllowedWhenAddingAMenuItem()
    {
        MenuItem::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test',
        ]);

        $this->postJson('/api/menu-items/add', [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test',
            'price' => 100,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testAMenuItemCanBeAdded()
    {
        $this->postJson('/api/menu-items/add', [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test',
            'price' => 100,
        ])->assertOk()
            ->assertJsonStructure(['menu_item_id']);

        $this->assertDatabaseHas('menu_items', [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test',
        ]);
    }

    public function testUpdateMenuItemValidationWorks()
    {
        $menuItem = MenuItem::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        $this->postJson('/api/menu-items/update/' . $menuItem->id, [
            'restaurant_id' => $this->restaurant->id,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }

    public function testSameNameNotAllowedWhenUpdatingAMenuItem()
    {
        MenuItem::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test',
        ]);
        $menuItem = MenuItem::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        $this->postJson('/api/menu-items/update/' . $menuItem->id, [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function testAMenuItemCanBeUpdated()
    {
        $menuItem = MenuItem::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        $this->postJson('/api/menu-items/update/' . $menuItem->id, [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test',
            'price' => 100,
        ])->assertOk();

        $this->assertDatabaseHas('menu_items', [
            'id' => $menuItem->id,
            'name' => 'Test',
        ]);
    }

    public function testOtherRestaurantMenuItemCannotBeUpdated()
    {
        $restaurant = Restaurant::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $menuItem = MenuItem::factory()->create([
            'restaurant_id' => $restaurant->id,
        ]);

        $this->postJson('/api/menu-items/update/' . $menuItem->id, [
            'restaurant_id' => $this->restaurant->id,
            'name' => 'Test',
            'price' => 100,
        ])->assertStatus(404);

        $this->assertDatabaseHas('menu_items', [
            'id' => $menuItem->id,
            'name' => $menuItem->name,
        ]);
    }

    public function testAMenuItemCanBeArchived()
    {
        $menuItem = MenuItem::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);

        $this->json('DELETE', '/api/menu-items/archive/' . $menuItem->id, [
            'restaurant_id' => $this->restaurant->id,
        ])
            ->assertOk();

        $this->assertSoftDeleted($menuItem);
    }

    public function testOtherRestaurantMenuItemCannotBeArchived()
    {
        $restaurant = Restaurant::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $menuItem = MenuItem::factory()->create([
            'restaurant_id' => $restaurant->id,
        ]);

        $this->json('DELETE', '/api/menu-items/archive/' . $menuItem->id, [
            'restaurant_id' => $this->restaurant->id,
        ])
            ->assertStatus(404);

        $this->assertDatabaseHas('menu_items', [
            'id' => $menuItem->id,
            $menuItem->getDeletedAtColumn() => null,
        ]);
    }
}