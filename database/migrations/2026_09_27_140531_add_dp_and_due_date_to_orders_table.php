<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('cashier_id')->constrained('users');
            $table->foreignId('shift_id')->nullable(); // Menghubungkan uang ke laci shift kasir
            $table->string('no_bukti_bayar')->unique(); // Contoh: BKM-TU-260927-0001
            
            $table->enum('payment_type', ['DP', 'PELUNASAN']);
            $table->enum('metode_pembayaran', ['cash', 'card', 'qris', 'transfer'])->default('cash');
            $table->decimal('nominal', 15, 0); // Jumlah uang yang diserahkan pelanggan
            
            // Kolom pendukung jika via Card/QRIS
            $table->string('bank_name')->nullable();
            $table->string('ref_no')->nullable(); // Trace number EDC / Ref QRIS
            
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_payments');
    }
};