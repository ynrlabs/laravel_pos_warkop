<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockOpname extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'opname_number',
        'type',
        'status',
        'opname_date',
        'completed_at',
        'approved_by',
        'approved_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function items()
    {
        return $this->hasMany(StockOpnameItem::class, 'opname_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function getTotalVarianceValueAttribute(): float
    {
        return $this->items->sum('variance_value');
    }
}