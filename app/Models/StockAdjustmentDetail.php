<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustmentDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_adjustment_details';

    protected $fillable = [
        'stock_adjustment_id',
        'product_id',
        'stock_system',
        'qty',
        'notes',
    ];

    public function stockAdjustment()
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}