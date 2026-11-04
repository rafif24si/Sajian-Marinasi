<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = ['id'];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
