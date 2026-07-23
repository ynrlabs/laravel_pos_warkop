<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topping extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'name',
        'slug',
        'price',
        'unit',
        'stock_qty',
        'track_stock',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_qty' => 'decimal:2',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_toppings')
            ->withPivot('default_qty', 'sort_order')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}