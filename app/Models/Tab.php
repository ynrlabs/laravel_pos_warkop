<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tab extends Model
{
    use HasFactory;

    protected $table = 'tabs';

    protected $fillable = [
        'outlet_id',
        'shift_id',
        'table_id',
        'queue_id',
        'customer_name',
        'customer_phone',
        'guest_count',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'service_charge_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'notes',
        'opened_by',
        'opened_at',
        'closed_at',
        'closed_by',
        'merged_from_tab_id',
        'merged_to_tab_id',
    ];

    protected $casts = [
        'guest_count' => 'integer',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'service_charge_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(QueueNumber::class, 'queue_id');
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function mergedFromTab(): BelongsTo
    {
        return $this->belongsTo(Tab::class, 'merged_from_tab_id');
    }

    public function mergedToTab(): BelongsTo
    {
        return $this->belongsTo(Tab::class, 'merged_to_tab_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TabItem::class);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeHold($query)
    {
        return $query->where('status', 'hold');
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['open', 'hold']);
    }

    public function getOutstandingAmountAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->table_id) {
            return $this->table?->code ?? 'Meja ' . $this->table_id;
        }
        if ($this->queue_id) {
            return $this->queue?->full_code ?? 'Antrian ' . $this->queue_id;
        }
        return $this->customer_name ?? 'Tab #' . $this->id;
    }
}