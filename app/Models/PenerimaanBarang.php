<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenerimaanBarang extends Model
{
    use HasFactory;

    protected $table = 'penerimaan_barang';

    protected $fillable = [
        'no_penerimaan',
        'no_po',
        'no_dokumen_supplier',
        'supplier_id',
        'tanggal_terima',
        'catatan',
        'total_item',
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

    public function items()
    {
        return $this->hasMany(PenerimaanBarangItem::class, 'penerimaan_barang_id');
    }
}