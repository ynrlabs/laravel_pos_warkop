<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TabItemModifier extends Model
{
    use HasFactory;

    protected $table = 'tab_item_modifiers';

    protected $fillable = [
        'tab_item_id',
        'modifier_id',
        'price_adjustment',
    ];

    protected $casts = [
        'price_adjustment' => 'decimal:2',
    ];

    public function tabItem(): BelongsTo
    {
        return $this->belongsTo(TabItem::class);
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(Modifier::class);
    }
}