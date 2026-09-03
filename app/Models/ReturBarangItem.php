<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturBarangItem extends Model
{
    use HasFactory;

    protected $table = 'retur_barang_items';

    protected $fillable = [
        'retur_barang_id',
        'product_id',
        'qty_retur',
        'harga_beli',
        'subtotal',
    ];

    public function returBarang()
    {
        return $this->belongsTo(ReturBarang::class, 'retur_barang_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}