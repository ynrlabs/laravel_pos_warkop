<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockMovement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'ingredient_id',
        'type',
        'quantity',
        'unit_id',
        'reference_type',
        'reference_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function ingredient()
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopeIn($query)
    {
        return $query->where('type', 'in');
    }

    public function scopeOut($query)
    {
        return $query->where('type', 'out');
    }
}