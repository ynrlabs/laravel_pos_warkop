<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockOpnameItem extends Model
{
    protected $table = 'stock_opname_items';

    protected $fillable = [
        'opname_id',
        'ingredient_id',
        'product_id',
        'product_variant_id',
        'system_qty',
        'physical_qty',
        'unit_id',
        'unit_cost',
        'batch_number',
        'expired_at',
        'notes',
        'counted_by',
        'counted_at',
    ];

    protected $casts = [
        'system_qty' => 'decimal:4',
        'physical_qty' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'expired_at' => 'date',
        'counted_at' => 'datetime',
    ];

    public function opname()
    {
        return $this->belongsTo(StockOpname::class, 'opname_id');
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function countedBy()
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function getVarianceQtyAttribute(): float
    {
        return $this->physical_qty - $this->system_qty;
    }

    public function getVarianceValueAttribute(): float
    {
        return $this->variance_qty * $this->unit_cost;
    }

    public function hasVariance(): bool
    {
        return $this->variance_qty !== 0;
    }
}