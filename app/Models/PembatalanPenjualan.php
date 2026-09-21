<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToBranch;

class PembatalanPenjualan extends Model
{
    use HasFactory, BelongsToBranch;
    

    protected $fillable = [
        'transaction_id',
        'user_id',
        'alasan',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}