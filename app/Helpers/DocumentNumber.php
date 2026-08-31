<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class DocumentNumber
{
    /**
     * Untuk dokumen transaksi
     * Contoh: PO-20260703-0001
     */
    public static function generate(
        string $table,
        string $field,
        string $prefix
    ): string {
        $today = date('Ymd');
        $like = $prefix . '-' . $today . '-%';

        // Urutkan berdasarkan id dokumen terakhir untuk hari ini agar aman dari sorting string
        $last = DB::table($table)
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
        return sprintf('%s-%s-%0' . $digit . 'd', $prefix, $today, $number);
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
}