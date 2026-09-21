<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // Contoh: PUSAT, KANOR, BRG
            $table->string('name', 100);
            $table->text('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Langsung buat Cabang Utama (Default) untuk menampung data eksisting
        DB::table('branches')->insert([
            'id' => 1,
            'code' => 'UTM',
            'name' => 'Toko Utama / Pusat',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
