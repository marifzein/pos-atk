<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\CommissionSetting;
use Carbon\Carbon;

class CheckCommissionScheme
{
    public function handle(Request $request, Closure $next)
    {
        // Hanya jalankan pengecekan jika user sudah login
        if (auth()->check()) {
            $setting = CommissionSetting::first();
            $sekarang = Carbon::now();

            // Jika sudah masuk bulan baru (tanggal 1 atau lebih) dan ada antrean skema baru
            if ($setting && $sekarang->day >= 1 && !is_null($setting->next_commission_type)) {
                
                // Eksekusi pemindahan data antrean ke data aktif
                $setting->update([
                    'commission_type' => $setting->next_commission_type,
                    'commission_flat_value' => $setting->next_commission_flat_value,
                    'commission_percentage_value' => $setting->next_commission_percentage_value,
                    
                    // Kosongkan kembali kolom antrean
                    'next_commission_type' => null,
                    'next_commission_flat_value' => null,
                    'next_commission_percentage_value' => null,
                ]);
            }
        }

        return $next($request);
    }
}