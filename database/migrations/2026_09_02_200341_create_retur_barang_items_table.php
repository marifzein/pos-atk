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
        Schema::create('retur_barang_items', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke retur_barang (ON DELETE CASCADE)
            $table->foreignId('retur_barang_id')
                  ->constrained('retur_barang')
                  ->onDelete('cascade');

            // Foreign Key ke products (tanpa cascade)
            $table->foreignId('product_id')
                  ->constrained('products');

            $table->integer('qty_retur');
            $table->decimal('harga_beli', 15, 2);
            $table->decimal('subtotal', 15, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_barang_items');
    }
};