<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftCashDetail extends Model
{
    use HasFactory;

    protected $table = 'shift_cash_details';

    protected $fillable = [
        'shift_id',
        'type',
        'denomination',
        'quantity',
        'notes',
    ];

    protected $casts = [
        'denomination' => 'decimal:2',
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->denomination * $this->quantity;
    }

    public static function getDenominations(): array
    {
        return [100000, 50000, 20000, 10000, 5000, 2000, 1000, 500, 200, 100];
    }
}