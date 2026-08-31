<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mengubah nama tabel lama ke nama baru yang lebih sreg
        Schema::rename('system_settings', 'commission_settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kembalikan ke nama lama jika di-rollback
        Schema::rename('commission_settings', 'system_settings');
    }
};