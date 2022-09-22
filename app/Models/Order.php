<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable =['restaurant_id','table_id','total','completed_at'];

    use HasFactory;

    public function items(){

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

}
