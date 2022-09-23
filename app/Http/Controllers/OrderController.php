<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Order;

class OrderController extends Controller
{

    public function listOpen(): array
    {
        $orders = Order::with('table')
            ->where('restaurant_id', request('restaurant_id'))
            ->open()
            ->get();

        return [
            'orders' => OrderResource::collection($orders),
        ];
    }

    public function listCompleted(): array
    {
        $orders = Order::with('table')
            ->where('restaurant_id', request('restaurant_id'))
            ->completed()
            ->get();

        return [
            'orders' => OrderResource::collection($orders),
        ];
    }

    

    public function bookATable(int $tableId): array
    {
        $table = Table::where('restaurant_id', request('restaurant_id'))
            ->where('id', $tableId)
            ->firstOrFail();

        $table->changeStatusTo(Occupied::class);

        $order = Order::create([
            'restaurant_id' => request('restaurant_id'),
            'table_id' => $table->id,
        ]);

        return [
            'order_id' => $order->id,
        ];
    }

    public function details(int $orderId): array
    {
        $order = Order::with(['table', 'items', 'items.menuItem'])
            ->where('restaurant_id', request('restaurant_id'))
            ->findOrFail($orderId);

        return [
            'order' => new OrderResource($order),
        ];
    }


}

