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
        Schema::create('retur_barang', function (Blueprint $table) {
            $table->id();
            $table->string('no_retur')->unique();

            // Foreign Key ke suppliers (tanpa cascade)
            $table->foreignId('supplier_id')->constrained('suppliers');

            $table->date('tanggal_retur');
            $table->string('catatan')->nullable();
            $table->integer('total_item');

            // Foreign Key ke users (tanpa cascade)
            $table->foreignId('user_id')->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_barang');
    }
};