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
        Schema::create('pembatalan_penjualans', function (Blueprint $table) {
            $table->id();
            
            // Foreign Key ke tabel transactions
            $table->foreignId('transaction_id')
                  ->constrained('transactions')
                  ->onDelete('cascade');
                  
            // Foreign Key ke tabel users (user yang membatalkan)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
                  
            $table->text('alasan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembatalan_penjualans');
    }
};