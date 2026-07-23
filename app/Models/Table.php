<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'number',
        'label',
        'capacity',
        'status',
        'is_active',
        'location',
        'sort_order',
        'qr_code_path',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'status' => 'string',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function tabs()
    {
        return $this->hasMany(Tab::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }
}