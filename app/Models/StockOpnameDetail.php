<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameDetail extends Model
{
    use HasFactory;

    protected $table = 'stock_opname_details';

    protected $fillable = [
        'stock_opname_id',
        'product_id',
        'stock_system',
        'stock_physical',
        'difference',
        'notes',
    ];

    public function stockOpname()
    {
        return $this->belongsTo(StockOpname::class, 'stock_opname_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}