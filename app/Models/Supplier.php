<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'pic',
        'phone',
        'address',
        'email',
        'catatan',
        'is_active',
    ];

    // Satu supplier bisa memasok banyak produk ATK
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
