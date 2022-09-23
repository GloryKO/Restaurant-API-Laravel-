<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['restaurant_id', 'table_id', 'total', 'completed_at'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    public function getStatusLabel(): string
    {
        return $this->completed_at ? 'Completed' : 'Open';
    }

    public function addItems(Collection $requestMenuItems, Collection $menuItems): void
    {
        $requestMenuItems->each(function ($requestMenuItem) use ($menuItems) {
            $menuItemPrice = $menuItems->firstWhere('id', $requestMenuItem['id'])->price;

            if ($orderItem = $this->items->firstWhere('menu_item_id', $requestMenuItem['id'])) {
                $orderItem->quantity += $requestMenuItem['quantity'];
                $orderItem->total = $menuItemPrice * $orderItem->quantity;
                $orderItem->save();

                return;
            }

            $this->items()
                ->create([
                    'menu_id' => $requestMenuItem['id'],
                    'quantity' => $requestMenuItem['quantity'],
                    'total' => $menuItemPrice * $requestMenuItem['quantity'],
                ]);
        });
    }

    public function changeItemsQuantity(Collection $requestMenuItems, Collection $menuItems): void
    {
        $requestMenuItems->each(function ($requestMenuItem) use ($menuItems) {
            $orderItem = $this->items->firstWhere('menu_id', $requestMenuItem['id']);
            $orderItem->quantity = $requestMenuItem['quantity'];
            $orderItem->total = $menuItems->firstWhere('id', $requestMenuItem['id'])->price * $orderItem->quantity;
            $orderItem->save();
        });
    }


    public function recalculateTotal()
    {
        $this->total = $this->items->sum('total');
        $this->save();
    }
}
