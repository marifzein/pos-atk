<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'barcode',
        'sku',
        'name',
        'brand',
        'type',
        'is_custom_price', 
        'supplier_id',
        'satuan',
        'purchase_price',
        'price',
        'stock',
        'min_stock',
        'is_active',
        'catatan',
    ];

    // Produk ini disuplai oleh...
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Produk ini bisa muncul di banyak detail pesanan
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}