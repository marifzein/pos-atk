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
        Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('barcode')->nullable()->unique(); // Untuk scan barcode ATK
        // SKU / Kode Internal Toko (Wajib ada dan unik)
        $table->string('sku')->unique();
        $table->string('name');
        $table->string('brand')->nullable(); // Merek ATK, misal: Joyko, Pilot, Sidu
        $table->enum('type', ['barang', 'jasa']); // Sesuai request: barang & jasa
        
        // Relasi ke supplier (nullable karena Jasa tidak punya supplier)
        $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
        
        $table->string('satuan')->default('pcs'); // Pcs, Lembar, Rim, Pack, dll
        
        // Urusan Duit & Stok (Semua pakai integer/bigInteger tanpa desimal)
        $table->bigInteger('purchase_price')->default(0); // HPP / Harga Beli awal (untuk average)
        $table->bigInteger('price')->default(0); // Harga Jual ke konsumen
        
        $table->integer('stock')->default(0); 
        $table->integer('min_stock')->default(0); // Pengingat kalau stok mau habis
        
        // Status & Catatan
        $table->boolean('is_active')->default(true); // Untuk turn ON/OFF produk di POS
        $table->text('catatan')->nullable(); // Keterangan tambahan produk
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
