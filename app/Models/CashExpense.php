<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashExpense extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Relasi ke Cabang
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Relasi ke Shift Aktif Kasir
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    // Relasi ke User Kasir yang bertugas
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Dokumen Penerimaan Barang (jika pembayaran supplier)
    public function penerimaanBarang()
    {
        return $this->belongsTo(PenerimaanBarang::class, 'reference_id');
    }
}