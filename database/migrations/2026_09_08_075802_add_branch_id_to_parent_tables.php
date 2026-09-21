<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Daftar tabel parent yang wajib dipasangi branch_id
        $tables = [
            'users',
            'transactions',
            'orders',
            'pembatalan_orders',
            'pembatalan_penjualans',
            'penerimaan_barang',
            'purchase_orders',
            'retur_barang',
            'stock_adjustments',
            'stock_opnames',
            'stock_movements',
            'shifts',
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'branch_id')) {
                        // default(1) memastikan semua data lama terikat ke Cabang Utama (PUSAT)
                        $table->foreignId('branch_id')
                              ->default(1)
                              ->after('id')
                              ->constrained('branches')
                              ->cascadeOnDelete();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'users', 'transactions', 'orders', 'pembatalan_orders', 
            'pembatalan_penjualans', 'penerimaan_barang', 'purchase_orders', 
            'retur_barang', 'stock_adjustments', 'stock_opnames', 
            'stock_movements', 'shifts'
        ];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                });
            }
        }
    }
};
