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
        Schema::table('commission_settings', function (Blueprint $table) {
            // Menambahkan kelompok kolom antrean untuk bulan depan (semuanya harus nullable)
            $table->enum('next_commission_type', ['full', 'flat', 'percentage'])
                  ->nullable()
                  ->after('commission_percentage_value'); // Biar posisinya rapi berurutan
            
            $table->integer('next_commission_flat_value')
                  ->nullable()
                  ->after('next_commission_type');
            
            $table->integer('next_commission_percentage_value')
                  ->nullable()
                  ->after('next_commission_flat_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_settings', function (Blueprint $table) {
            // Hapus kolom antrean jika di-rollback
            $table->dropColumn([
                'next_commission_type',
                'next_commission_flat_value',
                'next_commission_percentage_value'
            ]);
        });
    }
};