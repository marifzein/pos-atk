<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'commission_type',
        'commission_flat_value',
        'commission_percentage_value',
        'next_commission_type',
        'next_commission_flat_value',
        'next_commission_percentage_value',
    ];
}