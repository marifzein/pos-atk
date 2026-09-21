<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    use HasFactory;

    protected $table = 'product_stocks';

    protected $fillable = [
        'product_id',
        'branch_id',
        'stock',
        'min_stock',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'branch_id' => 'integer',
        'stock' => 'integer',
        'min_stock' => 'integer',
    ];

    /**
     * Relasi ke Master Produk
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Relasi ke Master Cabang
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}