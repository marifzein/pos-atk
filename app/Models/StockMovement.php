<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $table = 'stock_movements';

    protected $fillable = [
        'product_id',
        'type',
        'qty',
        'stock_before',
        'stock_after',
        'reference_no',
        'notes',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}