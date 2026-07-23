<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'name',
        'symbol',
        'type',
        'base_conversion',
        'is_base',
        'is_active',
    ];

    protected $casts = [
        'base_conversion' => 'decimal:4',
        'is_base' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}