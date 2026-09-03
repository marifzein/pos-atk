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
        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke stock_opnames (ON DELETE CASCADE)
            $table->foreignId('stock_opname_id')
                  ->constrained('stock_opnames')
                  ->onDelete('cascade');

            // Foreign Key ke products (tanpa cascade)
            $table->foreignId('product_id')
                  ->constrained('products');

            $table->integer('stock_system');
            $table->integer('stock_physical');
            $table->integer('difference');
            $table->string('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_opname_details');
    }
};