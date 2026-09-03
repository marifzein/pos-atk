<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;

    protected $table = 'stock_opnames';

    protected $fillable = [
        'opname_no',
        'opname_date',
        'user_name',
        'notes',
        'status',
        'finished_at',
    ];

    public function details()
    {
        return $this->hasMany(StockOpnameDetail::class, 'stock_opname_id');
    }
}