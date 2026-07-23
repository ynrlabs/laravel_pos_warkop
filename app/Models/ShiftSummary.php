<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSummary extends Model
{
    use HasFactory;

    protected $table = 'shift_summaries';

    protected $fillable = [
        'shift_id',
        'opening_cash',
        'cash_sales',
        'non_cash_sales',
        'refund_amount',
        'expected_cash',
        'actual_cash',
        'variance_notes',
        'transaction_count',
        'void_count',
        'refund_count',
    ];

    protected $casts = [
        'opening_cash' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'non_cash_sales' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'actual_cash' => 'decimal:2',
        'variance' => 'decimal:2',
        'transaction_count' => 'integer',
        'void_count' => 'integer',
        'refund_count' => 'integer',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function getVarianceAttribute(): float
    {
        return $this->actual_cash - $this->expected_cash;
    }

    public function hasVariance(): bool
    {
        return abs($this->variance) > 0.01;
    }

    public function getVarianceTypeAttribute(): string
    {
        if ($this->variance > 0) {
            return 'over';
        } elseif ($this->variance < 0) {
            return 'short';
        }
        return 'balanced';
    }

    public function getVarianceLabelAttribute(): string
    {
        $type = $this->variance_type;
        $amount = abs($this->variance);
        
        return match($type) {
            'over' => "Lebih Rp " . number_format($amount, 0, ',', '.'),
            'short' => "Kurang Rp " . number_format($amount, 0, ',', '.'),
            default => 'Selesai',
        };
    }

    public function getVarianceColorAttribute(): string
    {
        return match($this->variance_type) {
            'over' => 'success',
            'short' => 'danger',
            default => 'gray',
        };
    }
}