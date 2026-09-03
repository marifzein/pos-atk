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
        Schema::create('penerimaan_barang', function (Blueprint $table) {
            $table->id();
            $table->string('no_penerimaan')->unique();
            $table->string('no_po')->nullable();
            $table->string('no_dokumen_supplier')->nullable();
            
            // Foreign Keys
            $table->foreignId('supplier_id')->constrained('suppliers');
            
            $table->date('tanggal_terima');
            $table->text('catatan')->nullable();
            $table->integer('total_item')->default(0);
            
            $table->foreignId('user_id')->constrained('users');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimaan_barang');
    }
};