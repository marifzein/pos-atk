<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Jalankan Query Direct SQL untuk merubah opsi ENUM pada MariaDB/MySQL
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('order', 'batal', 'lunas') NOT NULL DEFAULT 'order'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert balik ke ENUM lama jika diperlukan rollback
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'paid', 'cancelled') NOT NULL DEFAULT 'pending'");
    }
};