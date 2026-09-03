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
        Schema::create('stock_adjustment_details', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke stock_adjustments (ON DELETE CASCADE)
            $table->foreignId('stock_adjustment_id')
                  ->constrained('stock_adjustments')
                  ->onDelete('cascade');

            // Foreign Key ke products (tanpa cascade)
            $table->foreignId('product_id')
                  ->constrained('products');

            $table->integer('stock_system');
            $table->integer('qty');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_details');
    }
};