<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoReceipt extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_id',
        'receipt_number',
        'receipt_date',
        'received_by',
        'notes',
    ];

    protected $casts = [
        'receipt_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function items()
    {
        return $this->hasMany(PoReceiptItem::class, 'receipt_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}