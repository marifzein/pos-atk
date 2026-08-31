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
        Schema::create('shifts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users'); // Kasir yang memegang shift
        
        // Uang Rupiah Bulat (Menggunakan Kesepakatan bigInteger)
        $table->bigInteger('starting_cash'); // Uang modal awal di laci
        $table->bigInteger('total_cash_sales')->default(0); // Total penjualan tunai selama shift berjalan
        $table->bigInteger('operational_expense')->default(0); // Pengeluaran tak terduga (pengamen, sampah, dll)
        $table->bigInteger('expected_cash')->default(0); // Rumus: starting_cash + total_cash_sales - operational_expense
        $table->bigInteger('ending_cash_actual')->nullable(); // Uang fisik yang dihitung manual oleh kasir saat closing
        
        $table->bigInteger('variance')->default(0); // Selisih (Selisih minus = kasir nomok, selisih plus = milik toko)
        $table->text('variance_reason')->nullable(); // Alasan selisih jika kasir mau kasih catatan tambahan
        
        $table->enum('status', ['open', 'closed'])->default('open');
        
        // Pencatatan Waktu Aktif Shift
        $table->timestamp('opened_at')->useCurrent();
        $table->timestamp('closed_at')->nullable();
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
