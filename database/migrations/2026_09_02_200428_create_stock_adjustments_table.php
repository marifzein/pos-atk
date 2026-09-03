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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_sa')->unique();
            $table->date('tgl_sa');

            // Foreign Key ke users (tanpa cascade)
            $table->foreignId('user_id')->constrained('users');

            $table->enum('status', ['draft', 'closed'])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamp('tgl_jam_selesai')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};