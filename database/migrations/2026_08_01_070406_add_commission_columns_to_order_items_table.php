<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // ID Operator yang mengerjakan item jasa ini
            $table->foreignId('operator_id')
                  ->nullable()
                  ->constrained('users') // Mengarah ke tabel users/karyawan
                  ->onDelete('set null');

            // Skema komisi yang aktif saat transaksi final
            $table->enum('commission_type', ['full', 'flat', 'percentage'])->nullable();

            // Menyimpan rate (Misal: 70 untuk persentase, atau 10000 untuk flat)
            $table->integer('commission_rate')->nullable();

            // Hasil akhir nominal Rupiah bersih yang diterima operator
            $table->bigInteger('commission_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu sebelum menghapus kolom
            $table->dropForeign(['operator_id']);
            
            // Hapus kolom snapshot
            $table->dropColumn([
                'operator_id',
                'commission_type',
                'commission_rate',
                'commission_amount'
            ]);
        });
    }
};