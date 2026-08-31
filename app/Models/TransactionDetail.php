<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'harga_beli',
        'kode_barang',
        'nama_barang',
        'harga',
        'qty',
        'subtotal',
    ];

    /**
     * Relasi ke Header Transaksi
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Relasi ke Produk
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}