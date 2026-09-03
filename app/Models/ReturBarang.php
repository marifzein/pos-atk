<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturBarang extends Model
{
    use HasFactory;

    protected $table = 'retur_barang';

    protected $fillable = [
        'no_retur',
        'supplier_id',
        'tanggal_retur',
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
        return $this->hasMany(ReturBarangItem::class, 'retur_barang_id');
    }
}