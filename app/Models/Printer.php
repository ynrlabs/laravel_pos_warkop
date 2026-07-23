<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Printer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'name',
        'type',
        'connection_type',
        'paper_width',
        'device_address',
        'ip_address',
        'port',
        'is_default',
        'is_active',
        'print_categories',
    ];

    protected $casts = [
        'paper_width' => 'integer',
        'port' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'print_categories' => 'array',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function kdsStations()
    {
        return $this->hasMany(KdsStation::class);
    }

    public function printJobs()
    {
        return $this->hasMany(PrintJob::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}