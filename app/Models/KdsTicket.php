<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KdsTicket extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'order_id',
        'order_item_id',
        'kds_station_id',
        'ticket_number',
        'type',
        'status',
        'items_count',
        'notes',
        'started_at',
        'completed_at',
        'recalled_at',
    ];

    protected $casts = [
        'items_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'recalled_at' => 'datetime',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function station()
    {
        return $this->belongsTo(KdsStation::class, 'kds_station_id');
    }

    public function items()
    {
        return $this->hasMany(KdsTicketItem::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}