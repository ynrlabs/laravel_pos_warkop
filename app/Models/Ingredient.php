<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'name',
        'slug',
        'unit_id',
        'current_stock',
        'min_stock',
        'max_stock',
        'avg_cost',
        'sku',
        'track_stock',
        'is_active',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'max_stock' => 'decimal:2',
        'avg_cost' => 'decimal:2',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function purchaseOrderItems()
    {
        return $this->hasMany(PoItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->where('current_stock', '<=', 'min_stock');
    }
}