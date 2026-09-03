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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Foreign Key ke products (ON DELETE CASCADE)
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');

            $table->string('type');
            $table->integer('qty');
            $table->integer('stock_before');
            $table->integer('stock_after');
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};