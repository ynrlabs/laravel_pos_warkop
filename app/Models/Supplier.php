<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'outlet_id',
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'npwp',
        'payment_terms',
        'bank_account',
        'bank_name',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'payment_terms' => 'integer',
        'is_active' => 'boolean',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}