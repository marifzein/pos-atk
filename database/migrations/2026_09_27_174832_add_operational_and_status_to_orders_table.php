<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Asal order: apakah staf biasa atau kasir
            $table->enum('order_source', ['staff', 'kasir'])
                  ->default('staff')
                  ->after('branch_id');

            // Estimasi selesai pekerjaan
            $table->dateTime('tgl_estimasi_selesai')
                  ->nullable()
                  ->after('catatan');

            // Nilai total kesepakatan order (kontrak barang)
            $table->decimal('total_amount', 15, 0)
                  ->default(0)
                  ->after('tgl_estimasi_selesai');

            // Status keuangan cukup pakai enum flag
            $table->enum('payment_status', ['unpaid', 'dp', 'lunas'])
                  ->default('unpaid')
                  ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_source',
                'tgl_estimasi_selesai',
                'total_amount',
                'payment_status',
            ]);
        });
    }
};