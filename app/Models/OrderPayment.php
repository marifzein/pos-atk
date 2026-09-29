<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    use HasFactory;

    protected $table = 'order_payments';

    protected $fillable = [
        'branch_id',
        'order_id',
        'cashier_id',
        'shift_id',
        'no_bukti_bayar',
        'payment_type',
        'metode_pembayaran',
        'nominal',
        'bank_name',
        'ref_no',
        'catatan',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}