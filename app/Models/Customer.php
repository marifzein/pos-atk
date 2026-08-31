<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'kode_pelanggan',
        'nama',
        'telepon',
        'alamat',
        'email',
        'catatan',
        'is_member',
        'status'
    ];

    // Member ini bisa punya banyak riwayat pesanan
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}