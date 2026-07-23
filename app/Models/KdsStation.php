<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KdsStation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'name',
        'slug',
        'type',
        'ip_address',
        'printer_id',
        'is_active',
        'sound_enabled',
        'auto_print',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sound_enabled' => 'boolean',
        'auto_print' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function printer()
    {
        return $this->belongsTo(Printer::class);
    }

    public function tickets()
    {
        return $this->hasMany(KdsTicket::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}