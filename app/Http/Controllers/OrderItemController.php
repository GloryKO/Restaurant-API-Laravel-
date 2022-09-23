<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderItemValidation;
use App\Http\Resources\OrderResource;
use App\Models\MenuItem;
use App\Models\Order;
use Illuminate\Support\Collection;

class OrderItemController extends Controller
{
    public function add(OrderItemValidation $request, int $orderId)
    {
        $order = Order::with('items')
            ->where('restaurant_id', $request->input('restaurant_id'))
            ->findOrFail($orderId);

        $requestMenuItems = collect($request->input('menu_items'));

        $menuItems = $this->fetchMenuItems($requestMenuItems);
        $order->addItems($requestMenuItems, $menuItems);
        $order->load(['table', 'items', 'items.menuItem']);

        $order->recalculateTotal();

        return [
            'order' => new OrderResources($order),
        ];
    }


    public function remove(int $orderId){
        $order = Order::with('items')
        ->where('restaurant_id',request('restaurant_id'))
        ->findOrFail($orderId);

        $requestMenuItemIds = collect(request('menu_item_ids'));
        $this->checkOrderItemExistence($requestMenuIds,$order);
        $order->items()
        ->where('menu_id',$requestMenuItemIds)
        ->delete();
        $order->load(['table','items','items.menuItem']);
        $order->recalculateTotal();

        return [
            'order'=> new OrderResource($order),
        ];

    }


    public function changeQuantity(OrderItemValidation $request,int $orderId){
        $order = Order::with('items')
        ->where('restaurant_id',$request->input('restaurant_id'))
        ->findOrFail($orderId);

        $requestMenuItems = collect($request->input('menu_items')); //get menu items and store in a collection

        $this->checkOrderItemExistence($requestMenuItems->pluck('id'),$order); /* check if the items exist in this particular order by calling
                                                                              the method to do that */

        $menuItems = $this->fetchMenuItems($requestMenuItems);  //fetch the menu items
        $order->changeItemsQuantity($requestMenuItems,$menuItems);  //call method to change the quantity of the menu items.
        $order->load(['table','items','items.menuItem']);
        $order->recalculateTotal();

        return [
            'order' => new OrderResource($order),
        ];


    }

    private function checkOrderItemExistence(Collection $requestMenuItemIds,Order $order){
        $requestMenuItemIds->each(function ($requestMenuItemId) use ($order){
                if(!$order->items->contains('menu_id',$requestMenuItemId)){
                    abort(417,'The menu is not part of the order items');
                }
        });
    }

    private function fetchMenuItems(Collection $requestMenuItems): Collection
    {
        $menuItems = Menu::where('restaurant_id', request('restaurant_id'))
            ->whereIn('id', $requestMenuItems->pluck('id'))
            ->get(['id', 'price']);

        if ($menuItems->count() !== $requestMenuItems->count()) {
            abort(417, 'Some of the menu items could not be fetched. Please check menu items again.');
        }

        return $menuItems;
    }
}
