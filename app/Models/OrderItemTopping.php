<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemTopping extends Model
{
    protected $fillable = [
        'order_item_id',
        'topping_id',
        'topping_name',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function topping()
    {
        return $this->belongsTo(Topping::class);
    }
}
