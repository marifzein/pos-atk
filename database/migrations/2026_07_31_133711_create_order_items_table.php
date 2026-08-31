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
        Schema::create('order_items', function (Blueprint $table) {
        $table->id();
        
        // Ditulis lengkap agar eksplisit dan mudah dipahami
        $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
        
        // Sekarang WAJIB diisi (tidak nullable), karena semua jasa/barang harus terdaftar di master
        $table->foreignId('product_id')->constrained('products'); 
        
        $table->string('item_name'); // Tetap ada untuk menyimpan snapshot nama produk saat transaksi
        $table->integer('qty')->default(1);
        
        // Kunci Akunting Rupiah Bulat
        $table->bigInteger('purchase_price')->default(0); // HPP diambil dari tabel products
        $table->bigInteger('unit_price'); // Harga jual (Bisa fix dari master, atau diubah manual oleh desainer di UI)
        $table->bigInteger('subtotal'); // qty * unit_price
        
        // Kolom sakti tempat desainer/operator nulis detail khusus (misal: "Ukuran 3x1 meter, revisi 3x")
        $table->text('notes')->nullable(); 
        
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
