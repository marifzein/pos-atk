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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->foreignId('cashier_id')->constrained('users');
            
            // 💡 RELASI PENTING:
            $table->foreignId('shift_id')->constrained('shifts'); // Menandakan transaksi ini masuk ke shift mana
            
            $table->bigInteger('amount_paid');
            $table->bigInteger('change');
            $table->enum('payment_method', ['cash', 'qris', 'transfer'])->default('cash');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
