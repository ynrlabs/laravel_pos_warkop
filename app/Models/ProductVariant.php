<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'product_id',
        'name',
        'slug',
        'price_adjustment',
        'stock_qty',
        'track_stock',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
        'stock_qty' => 'integer',
        'track_stock' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getPriceAttribute()
    {
        return $this->product->base_price + $this->price_adjustment;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}