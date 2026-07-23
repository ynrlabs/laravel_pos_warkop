<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'supplier_id',
        'po_number',
        'status',
        'order_date',
        'expected_date',
        'received_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'shipping_cost',
        'total_amount',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
        'received_by',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PoItem::class);
    }

    public function receipts()
    {
        return $this->hasMany(PoReceipt::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'confirmed', 'partial_received']);
    }

    public function getTotalReceivedAttribute(): float
    {
        return $this->items->sum('quantity_received');
    }

    public function getTotalOrderedAttribute(): float
    {
        return $this->items->sum('quantity_ordered');
    }

    public function isFullyReceived(): bool
    {
        foreach ($this->items as $item) {
            if ($item->quantity_received < $item->quantity_ordered) {
                return false;
            }
        }
        return true;
    }
}