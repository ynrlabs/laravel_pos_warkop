<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TabItem extends Model
{
    use HasFactory;

    protected $table = 'tab_items';

    protected $fillable = [
        'tab_id',
        'product_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'notes',
        'status',
        'kds_ticket_id',
        'added_by',
        'added_at',
        'served_at',
        'void_reason',
        'voided_by',
        'voided_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'line_subtotal' => 'decimal:2',
        'added_at' => 'datetime',
        'served_at' => 'datetime',
        'voided_at' => 'datetime',
    ];

    public function tab(): BelongsTo
    {
        return $this->belongsTo(Tab::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function kdsTicket(): BelongsTo
    {
        return $this->belongsTo(KdsTicket::class);
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(TabItemModifier::class);
    }

    public function toppings(): HasMany
    {
        return $this->hasMany(TabItemTopping::class);
    }

    public function getLineSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }

    public function getTotalWithModifiersAttribute(): float
    {
        $modifierTotal = $this->modifiers->sum('price_adjustment');
        return $this->line_subtotal + $modifierTotal;
    }

    public function getTotalWithToppingsAttribute(): float
    {
        $toppingTotal = $this->toppings->sum('line_subtotal');
        return $this->line_subtotal + $toppingTotal;
    }

    public function getGrandTotalAttribute(): float
    {
        $modifierTotal = $this->modifiers->sum('price_adjustment');
        $toppingTotal = $this->toppings->sum('line_subtotal');
        return $this->line_subtotal + $modifierTotal + $toppingTotal;
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->product->name;
        if ($this->variant) {
            $name .= ' - ' . $this->variant->name;
        }
        return $name;
    }

    public function getModifiersDisplayAttribute(): string
    {
        return $this->modifiers->map(fn($m) => $m->modifier_name . ($m->price_adjustment > 0 ? ' (+' . number_format($m->price_adjustment, 0, ',', '.') . ')' : ''))
            ->implode(', ');
    }

    public function getToppingsDisplayAttribute(): string
    {
        return $this->toppings->map(fn($t) => $t->topping_name . ' x' . $t->quantity . ($t->unit_price > 0 ? ' (+' . number_format($t->unit_price, 0, ',', '.') . ')' : ''))
            ->implode(', ');
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isServed(): bool
    {
        return $this->status === 'served';
    }
}