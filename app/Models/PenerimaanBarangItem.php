<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanBarangItem extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_barang_items';

    protected $fillable = [
        'penerimaan_barang_id',
        'product_id',
        'qty_po',
        'qty_terima',
        'harga_beli',
        'subtotal',
    ];

    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarang::class, 'penerimaan_barang_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}