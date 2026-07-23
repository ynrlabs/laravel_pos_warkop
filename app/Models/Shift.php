<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shift extends Model
{
    use HasFactory;

    protected $table = 'shifts';

    protected $fillable = [
        'outlet_id',
        'shift_definition_id',
        'user_id',
        'shift_date',
        'status',
        'opened_at',
        'closed_at',
        'reconciled_at',
        'reconciled_by',
        'notes',
    ];

    protected $casts = [
        'shift_date' => 'date',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'reconciled_at' => 'datetime',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function shiftDefinition(): BelongsTo
    {
        return $this->belongsTo(ShiftDefinition::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    public function cashDetails(): HasMany
    {
        return $this->hasMany(ShiftCashDetail::class);
    }

    public function openingCashDetails(): HasMany
    {
        return $this->hasMany(ShiftCashDetail::class)->where('type', 'opening');
    }

    public function closingCashDetails(): HasMany
    {
        return $this->hasMany(ShiftCashDetail::class)->where('type', 'closing');
    }

    public function summary(): HasOne
    {
        return $this->hasOne(ShiftSummary::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function tabs(): HasMany
    {
        return $this->hasMany(Tab::class);
    }

    public function scopeOpened($query)
    {
        return $query->where('status', 'opened');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeReconciled($query)
    {
        return $query->where('status', 'reconciled');
    }

    public function getOpeningCashAttribute(): float
    {
        return $this->openingCashDetails->sum('subtotal');
    }

    public function getClosingCashAttribute(): float
    {
        return $this->closingCashDetails->sum('subtotal');
    }

    public function getTotalCashSalesAttribute(): float
    {
        return $this->orders()
            ->whereHas('payments', fn($q) => $q->where('method', 'cash')->where('status', 'paid'))
            ->sum('total');
    }

    public function getTotalNonCashSalesAttribute(): float
    {
        return $this->orders()
            ->whereHas('payments', fn($q) => $q->where('method', '!=', 'cash')->where('status', 'paid'))
            ->sum('total');
    }

    public function getRefundAmountAttribute(): float
    {
        return $this->orders()
            ->where('status', 'refunded')
            ->sum('total');
    }

    public function getTransactionCountAttribute(): int
    {
        return $this->orders()->where('status', 'paid')->count();
    }

    public function getVoidCountAttribute(): int
    {
        return $this->orders()->where('status', 'void')->count();
    }

    public function getRefundCountAttribute(): int
    {
        return $this->orders()->where('status', 'refunded')->count();
    }
}