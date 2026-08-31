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
    Schema::create('suppliers', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Nama PT / Toko Grosir Supplier
        $table->string('pic')->nullable(); // Person In Charge / Nama Sales-nya
        $table->string('phone')->nullable(); // Nomor HP Sales/Kantor
        $table->text('address')->nullable(); // Alamat Gudang/Kantor Supplier
        $table->text('email')->nullable(); // email
        $table->text('catatan')->nullable(); // Catatan tambahan (misal: "Bisa tempo 30 hari")
        $table->boolean('is_active')->default(true); // Untuk menonaktifkan supplier jika sudah tidak kerja sama
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
