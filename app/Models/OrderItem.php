<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 
        'product_id', 
        'item_name', 
        'qty', 
        'purchase_price', 
        'unit_price', 
        'subtotal', 
        'notes'];

    // Detail ini bagian dari pesanan nomor...
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Detail ini mengacu pada produk master...
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
