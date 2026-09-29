<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DocumentNumber
{
    /**
     * Untuk dokumen transaksi transaksi berbasis user login
     * Contoh: PO-260703-0001
     */
    public static function generate(
        string $table,
        string $field,
        string $prefix
    ): string {
        $user = Auth::user();

    // STRICT CHECK: Wajib ada user login dan punya cabang
        if (!$user || !$user->branch_id || !$user->branch) {
            throw new \Exception('Maaf, cabang tidak terdeteksi. Silahkan login ulang.');
        }

        $branchId   = $user->branch_id;
        $branchCode = strtoupper($user->branch->code);

        // 2. Susun Prefix Gabungan: WO-PO
        $fullPrefix = $prefix . '-' . $branchCode;

        $today = date('ymd');
        $like = $fullPrefix . '-' . $today . '-%';

        // Urutkan berdasarkan id dokumen terakhir untuk hari ini agar aman dari sorting string
        $last = DB::table($table)
            ->where('branch_id', $branchId)
            ->where($field, 'like', $like)
            ->orderByDesc('id') // Menggunakan 'id' jauh lebih aman daripada urut string
            ->value($field);

        if (!$last) {
            $number = 1;
        } else {
            // $number = (int) substr($last, -5) + 1;
            $parts = explode('-', $last);
            $number = (int) end($parts) + 1;
        }

        $digit = ($number > 9999) ? strlen((string)$number) : 4;
        return sprintf('%s-%s-%0' . $digit . 'd', $fullPrefix, $today, $number);
    }

    /**
     * Untuk master data
     * Contoh: CUST0001, SUP0001
     */
    public static function generateMaster(
        string $table,
        string $field,
        string $prefix,
        int $digit = 4
    ): string {
        // Ambil data terakhir berdasarkan 'id' agar increment nomor urutnya tidak kacau
        $last = DB::table($table)
            ->where($field, 'like', $prefix . '%')
            ->orderByDesc('id') 
            ->value($field);

        if (!$last) {
            $number = 1;
        } else {
            $numericPart = substr($last, strlen($prefix));
            $number = (int) $numericPart + 1;
        }

         return $prefix .
            str_pad(
                $number,
                $digit,
                '0',
                STR_PAD_LEFT
            );
    }

    // ====================
    public static function generate_custom(
        string $table,
        string $field,
        string $prefix,
        int $branchId
    ): string {
        // Cari data cabang berdasarkan branch_id
        $branch = DB::table('branches')->where('id', $branchId)->first();

        if (!$branch) {
            throw new \Exception('Cabang tidak ditemukan.');
        }

        $branchCode = strtoupper($branch->code);

        // Format Prefix Gabungan: PB-TU / PB-PO / PB-PM
        $fullPrefix = $prefix . '-' . $branchCode;

        $today = date('ymd');
        $like = $fullPrefix . '-' . $today . '-%';

        // Cari transaksi terakhir untuk hari ini berdasarkan branch_id
        $last = DB::table($table)
            ->where('branch_id', $branchId)
            ->where($field, 'like', $like)
            ->orderByDesc('id')
            ->value($field);

        if (!$last) {
            $number = 1;
        } else {
            $parts = explode('-', $last);
            $number = (int) end($parts) + 1;
        }

        $digit = ($number > 9999) ? strlen((string)$number) : 4;
        return sprintf('%s-%s-%0' . $digit . 'd', $fullPrefix, $today, $number);
    }

    
}

