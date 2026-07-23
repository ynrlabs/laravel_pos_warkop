<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoItem extends Model
{
    protected $table = 'po_items';

    protected $fillable = [
        'po_id',
        'ingredient_id',
        'quantity_ordered',
        'quantity_received',
        'unit_id',
        'unit_price',
        'discount_percent',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'notes',
    ];

    protected $casts = [
        'quantity_ordered' => 'decimal:4',
        'quantity_received' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'discount_percent' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function receiptItems()
    {
        return $this->hasMany(PoReceiptItem::class, 'po_item_id');
    }

    public function getLineTotalAttribute(): float
    {
        return ($this->quantity_ordered * $this->unit_price) - $this->discount_amount + $this->tax_amount;
    }

    public function getRemainingQuantityAttribute(): float
    {
        return $this->quantity_ordered - $this->quantity_received;
    }

    public function isFullyReceived(): bool
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }
}