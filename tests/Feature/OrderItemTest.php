<?php

namespace Tests\Feature;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    private $restaurant;

    private $table;

    private $menuItem;

    private $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->restaurant = Restaurant::factory()->create([
            'user_id' => $this->user->id,
        ]);
        $this->menuItem = MenuItem::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);
        $this->table = Table::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);
        $this->order = Order::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'table_id' => $this->table->id,
        ]);

        Sanctum::actingAs($this->user, ['*']);
    }

    public function testAddOrderItemsValidationWorks()
    {
        $this->postJson('/api/order-items/add/' . $this->order->id, [
            'restaurant_id' => $this->restaurant->id,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['menu_items']);
    }

    public function testOtherRestaurantOrderItemsCannotBeAdded()
    {
        $order = Order::factory()->create();

        $this->postJson('/api/order-items/add/' . $order->id, [
            'restaurant_id' => $this->restaurant->id,
            'menu_items' => [[
                'id' => $this->menuItem->id,
                'quantity' => 1,
            ]],
        ])->assertStatus(404);
    }

    public function testOtherRestaurantenMenuItemsCannotBeAdded()
    {
        $menuItem = MenuItem::factory()->create();

        $this->postJson('/api/order-items/add/' . $this->order->id, [
            'restaurant_id' => $this->restaurant->id,
            'menu_items' => [[
                'id' => $menuItem->id,
                'quantity' => 1,
            ]],
        ])->assertStatus(417);
    }

    public function testOrderItemsCanBeAdded()
    {
        $this->postJson('/api/order-items/add/' . $this->order->id, [
            'restaurant_id' => $this->restaurant->id,
            'menu_items' => [[
                'id' => $this->menuItem->id,
                'quantity' => 1,
            ]],
        ])->assertOk()
            ->assertJson(
                fn (AssertableJson $json) => $json->where('order.id', $this->order->id)
                    ->where('order.table.id', $this->table->id)
                    ->where('order.status', 'Open')
                    ->has('order.items', 1)
                    ->etc()
            );

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'total' => $this->menuItem->price,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $this->order->id,
            'menu_item_id' => $this->menuItem->id,
            'total' => $this->menuItem->price,
        ]);
    }

    public function testOrderItemQuantityIsUpdatedWhenAddedAgain()
    {
        $this->postJson('/api/order-items/add/' . $this->order->id, [
            'restaurant_id' => $this->restaurant->id,
            'menu_items' => [[
                'id' => $this->menuItem->id,
                'quantity' => 1,
            ]],
        ])->assertOk();

        $this->postJson('/api/order-items/add/' . $this->order->id, [
            'restaurant_id' => $this->restaurant->id,
            'menu_items' => [[
                'id' => $this->menuItem->id,
                'quantity' => 1,
            ]],
        ])->assertOk();

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'total' => $this->menuItem->price * 2,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $this->order->id,
            'menu_item_id' => $this->menuItem->id,
            'quantity' => 2,
            'total' => $this->menuItem->price * 2,
        ]);
    }
}
