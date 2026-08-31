<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_nota',
        'order_id',
        'cashier_id',
        'customer_id', // ditambahkan jika ada kolom customer_id di tabel transactions
        'shift_id',
        'subtotal',
        'diskon',
        'grand_total',
        'cash',
        'voucher',
        'card',
        'hutang',
        'kembalian',
        'catatan',
        'status',
    ];

    /**
     * Relasi ke Detail Transaksi
     */
    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    /**
     * Relasi ke Kasir (User)
     */
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    /**
     * Relasi ke Customer
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relasi ke Order / Pesanan Jasa (jika ada)
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}