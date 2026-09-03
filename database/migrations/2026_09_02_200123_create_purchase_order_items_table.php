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
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke purchase_orders (ON DELETE CASCADE)
            $table->foreignId('purchase_order_id')
                  ->constrained('purchase_orders')
                  ->onDelete('cascade');

            // Foreign Key ke products (ON DELETE CASCADE)
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');

            $table->integer('qty');
            $table->decimal('price', 15, 0);
            $table->decimal('subtotal', 15, 0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};