<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'category_id',
        'name',
        'slug',
        'description',
        'image_path',
        'type',
        'base_price',
        'cost_price',
        'stock_qty',
        'min_stock',
        'max_stock',
        'track_stock',
        'is_active',
        'is_bestseller',
        'is_combo',
        'prep_time_minutes',
        'kds_station',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'is_bestseller' => 'boolean',
        'is_combo' => 'boolean',
        'metadata' => 'array',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function modifierGroups()
    {
        return $this->belongsToMany(ModifierGroup::class, 'product_modifier_groups')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function toppings()
    {
        return $this->belongsToMany(Topping::class, 'product_toppings')
            ->withPivot('default_qty', 'sort_order')
            ->withTimestamps();
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeBestseller($query)
    {
        return $query->where('is_bestseller', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function getDefaultVariantAttribute()
    {
        return $this->variants()->where('is_default', true)->first();
    }

    public function getEffectivePriceAttribute()
    {
        $defaultVariant = $this->default_variant;
        return $this->base_price + ($defaultVariant?->price_adjustment ?? 0);
    }
}