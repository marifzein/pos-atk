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
        Schema::create('penerimaan_barang_items', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke penerimaan_barang (ON DELETE CASCADE)
            $table->foreignId('penerimaan_barang_id')
                  ->constrained('penerimaan_barang')
                  ->onDelete('cascade');

            // Foreign Key ke products
            $table->foreignId('product_id')
                  ->constrained('products');

            $table->integer('qty_po')->default(0);
            $table->integer('qty_terima');
            $table->decimal('harga_beli', 15, 0);
            $table->decimal('subtotal', 15, 0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barang_items');
    }
};