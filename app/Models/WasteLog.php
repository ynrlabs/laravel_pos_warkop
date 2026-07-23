<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WasteLog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'waste_number',
        'waste_date',
        'waste_time',
        'ingredient_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_id',
        'unit_cost',
        'reason',
        'reason_detail',
        'photo_path',
        'reported_by',
        'approved_by',
        'approved_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'waste_date' => 'date',
        'waste_time' => 'time',
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'approved_at' => 'datetime',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
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

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getTotalCostAttribute(): float
    {
        return $this->quantity * $this->unit_cost;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}