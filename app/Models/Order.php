<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Order extends Model
{
    protected $fillable = ['no_pesanan', 'operator_id', 'customer_id', 'customer_name_manual', 'status', 'catatan'];

    // Relasi ke Operator/Desainer yang menginput
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    // Relasi ke Customer (jika dia member)
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Satu pesanan punya banyak item detail (Barang/Jasa)
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Tambahkan alias ini
    public function orderItems()
    {
        return $this->items();
    }

    // Satu pesanan yang sukses akan menghasilkan satu transaksi lunas
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Accessor Humanis Pendekatan Customer Care untuk Layar Kasir & Cetak Struk
     */
    protected function namaPelanggan(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->customer_id && $this->customer) {
                    return $this->customer->nama . ' (Member)';
                }
                return $this->customer_name_manual ?? 'Pelanggan Umum';
            }
        );
    }

    // pembatalan orders
    public function pembatalan()
    {
        return $this->hasOne(PembatalanOrder::class, 'order_id');
    }
}
