<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoundingRule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'name',
        'rounding_value',
        'type',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'rounding_value' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}