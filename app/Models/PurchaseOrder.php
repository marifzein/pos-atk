<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'po_date',
        'status',
        'total',
        'notes',
        'user_id',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // public function items()
    // {
    //     return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    // }
    public function purchaseOrderItems()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }
}