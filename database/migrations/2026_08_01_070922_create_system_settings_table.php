<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            
            // Menyimpan tipe komisi aktif
            $table->enum('commission_type', ['full', 'flat', 'percentage'])->default('full');
            
            // Menyimpan nilai nominal rupiah jika tipe 'flat'
            $table->integer('commission_flat_value')->nullable();
            
            // Menyimpan nilai persen operator jika tipe 'percentage' (Contoh: 70 berarti 70%)
            $table->integer('commission_percentage_value')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};