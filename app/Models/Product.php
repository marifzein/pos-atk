<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class, 'product_id');
    }

    // supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Produk ini bisa muncul di banyak detail pesanan
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

     public function stockMovements()
    {
        return $this->hasMany(
            StockMovement::class
        );
    }

     public function stockOpnameDetails()
    {
        return $this->hasMany(
            StockOpnameDetail::class
        );
    }
}