<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'key',
        'value',
        'type',
        'description',
        'group',
        'is_public',
    ];

    protected $casts = [
        'value' => 'string',
        'is_public' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}