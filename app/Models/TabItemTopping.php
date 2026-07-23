<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TabItemTopping extends Model
{
    use HasFactory;

    protected $table = 'tab_item_toppings';

    protected $fillable = [
        'tab_item_id',
        'topping_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'line_subtotal' => 'decimal:2',
    ];

    public function tabItem(): BelongsTo
    {
        return $this->belongsTo(TabItem::class);
    }

    public function topping(): BelongsTo
    {
        return $this->belongsTo(Topping::class);
    }

    public function getLineSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}