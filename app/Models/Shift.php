<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $fillable = [
        'user_id',
        'starting_cash',
        'total_cash_sales',
        'operational_expense',
        'expected_cash',
        'ending_cash_actual',
        'variance',
        'variance_reason',
        'status',
        'opened_at',
        'closed_at',
    ];


     // Mengatur casting tipe data agar otomatis rapi saat dihitung di PHP
    protected $casts = [
        'starting_cash' => 'decimal:2',
        'total_cash_sales' => 'decimal:2',
        'operational_expense' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'ending_cash_actual' => 'decimal:2',
        'variance' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];


    // Kasir pemilik shift
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Semua transaksi yang terkumpul selama shift ini aktif
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}