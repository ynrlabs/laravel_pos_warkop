<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'name',
        'slug',
        'icon',
        'color',
        'sort_order',
        'is_active',
        'is_kds_station',
        'kds_station_name',
    ];

    protected $casts = [
        'color' => 'string',
        'is_active' => 'boolean',
        'is_kds_station' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeKdsStations($query)
    {
        return $query->where('is_kds_station', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}