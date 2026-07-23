<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'outlet_id',
        'order_id',
        'payment_number',
        'method',
        'provider',
        'provider_transaction_id',
        'provider_reference',
        'amount',
        'status',
        'paid_at',
        'expired_at',
        'qr_code_data',
        'qr_image_path',
        'cash_received',
        'cash_change',
        'ewallet_phone',
        'notes',
        'processed_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cash_received' => 'decimal:2',
        'cash_change' => 'decimal:2',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PaymentDetail::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function isCash(): bool
    {
        return $this->method === 'cash';
    }

    public function isQris(): bool
    {
        return in_array($this->method, ['qris_dynamic', 'qris_static']);
    }

    public function isEwallet(): bool
    {
        return in_array($this->method, ['ewallet_gopay', 'ewallet_shopeepay', 'ewallet_dana', 'ewallet_ovo']);
    }

    public function isEdc(): bool
    {
        return in_array($this->method, ['edc_debit', 'edc_credit']);
    }

    public function isDigital(): bool
    {
        return $this->isQris() || $this->isEwallet() || $this->isEdc();
    }

    public function getMethodLabelAttribute(): string
    {
        return match($this->method) {
            'cash' => 'Tunai',
            'qris_dynamic' => 'QRIS Dinamis',
            'qris_static' => 'QRIS Statis',
            'ewallet_gopay' => 'GoPay',
            'ewallet_shopeepay' => 'ShopeePay',
            'ewallet_dana' => 'DANA',
            'ewallet_ovo' => 'OVO',
            'edc_debit' => 'EDC Debit',
            'edc_credit' => 'EDC Kredit',
            'hutang' => 'Hutang/Piutang',
            'voucher' => 'Voucher',
            'other' => 'Lainnya',
            default => ucfirst($this->method),
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Menunggu',
            'paid' => 'Lunas',
            'failed' => 'Gagal',
            'expired' => 'Kedaluwarsa',
            'refunded' => 'Direfund',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'expired' => 'gray',
            'refunded' => 'info',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function isExpired(): bool
    {
        return $this->expired_at && now()->gt($this->expired_at);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(?string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason ? ($this->notes ? $this->notes . ' | ' . $reason : $reason) : $this->notes,
        ]);
    }
}