<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierGroup extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'name',
        'slug',
        'selection_type',
        'is_required',
        'min_selection',
        'max_selection',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'selection_type' => 'string',
        'is_required' => 'boolean',
        'min_selection' => 'integer',
        'max_selection' => 'integer',
        'is_active' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function modifiers()
    {
        return $this->hasMany(Modifier::class)->ordered();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_modifier_groups')
            ->withPivot('sort_order')
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