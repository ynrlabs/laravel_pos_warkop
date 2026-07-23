<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PoReceiptItem extends Model
{
    protected $table = 'po_receipt_items';

    protected $fillable = [
        'receipt_id',
        'po_item_id',
        'ingredient_id',
        'quantity_received',
        'unit_id',
        'unit_cost',
        'batch_number',
        'expired_at',
        'quality_status',
        'notes',
    ];

    protected $casts = [
        'quantity_received' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'expired_at' => 'date',
    ];

    public function receipt()
    {
        return $this->belongsTo(PoReceipt::class, 'receipt_id');
    }

    public function poItem()
    {
        return $this->belongsTo(PoItem::class, 'po_item_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function getTotalCostAttribute(): float
    {
        return $this->quantity_received * $this->unit_cost;
    }
}