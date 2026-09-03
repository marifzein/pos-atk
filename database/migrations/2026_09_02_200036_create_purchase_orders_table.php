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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();

            // Foreign Key ke suppliers (ON DELETE CASCADE)
            $table->foreignId('supplier_id')
                  ->constrained('suppliers')
                  ->onDelete('cascade');

            $table->date('po_date');
            $table->enum('status', ['DRAFT', 'ORDERED', 'RECEIVED', 'CANCELLED'])->default('DRAFT');
            $table->decimal('total', 15, 0)->default(0);
            $table->text('notes')->nullable();

            // Foreign Key ke users (ON DELETE CASCADE)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};