<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $table = 'stock_adjustments';

    protected $fillable = [
        'nomor_sa',
        'tgl_sa',
        'user_id',
        'status',
        'catatan',
        'tgl_jam_selesai',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(StockAdjustmentDetail::class, 'stock_adjustment_id');
    }
}