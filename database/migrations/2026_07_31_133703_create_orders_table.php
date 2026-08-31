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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // Tetap ada untuk ID internal database (Primary Key)
            
            // Kolom khusus untuk nomor antrean format: WO-yymmdd-0001
            $table->string('no_pesanan')->unique(); 
            
            $table->foreignId('operator_id')->constrained('users'); // Siapa desainer/operatornya
            // Relasi ke tabel customers, set nullable karena non-member diisi NULL
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null'); 
            //  Jika non-member, namanya langsung diketik manual di sini
            $table->string('customer_name_manual')->nullable();
            // Status pesanan di kounter
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending');
            // buat alasan cancel atau keterangan lain
            $table->text('catatan')->nullable();
            // Otomatis membuat kolom created_at & updated_at (Menyimpan Tanggal + Jam Input secara presisi)
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
