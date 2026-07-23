<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KdsTicketItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'kds_ticket_id',
        'order_item_id',
        'product_name',
        'variant_name',
        'quantity',
        'notes',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function ticket()
    {
        return $this->belongsTo(KdsTicket::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function modifiers()
    {
        return $this->hasMany(KdsTicketItemModifier::class);
    }

    public function toppings()
    {
        return $this->hasMany(KdsTicketItemTopping::class);
    }
}