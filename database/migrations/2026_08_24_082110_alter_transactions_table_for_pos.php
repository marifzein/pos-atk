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
        Schema::table('transactions', function (Blueprint $table) {
            // 1. Hapus kolom-kolom lama yang tidak digunakan
            $table->dropColumn(['amount_paid', 'change', 'payment_method']);

            // 2. Tambah kolom no_nota (Unique) setelah id
            $table->string('no_nota')->unique()->after('id');

            // 3. Modifikasi order_id agar opsional (nullable) & terhubung ke tabel orders jika belum
            $table->unsignedBigInteger('order_id')->nullable()->change();

            // 4. Tambahkan kolom nominal pembayaran & transaksi setelah shift_id
            $table->decimal('subtotal', 15, 0)->default(0)->after('shift_id');
            $table->decimal('diskon', 15, 0)->default(0)->after('subtotal');
            $table->decimal('grand_total', 15, 0)->default(0)->after('diskon');
            $table->decimal('cash', 15, 0)->default(0)->after('grand_total');
            $table->decimal('voucher', 15, 0)->default(0)->after('cash');
            $table->decimal('card', 15, 0)->default(0)->after('voucher');
            $table->decimal('hutang', 15, 0)->default(0)->after('card');
            $table->decimal('kembalian', 15, 0)->default(0)->after('hutang');
            $table->text('catatan')->nullable()->after('kembalian');
            
            // 5. Tambah status enum ('INV', 'LUNAS', 'BATAL')
            $table->enum('status', ['INV', 'LUNAS', 'BATAL'])->default('LUNAS')->after('catatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'no_nota',
                'subtotal',
                'diskon',
                'grand_total',
                'cash',
                'voucher',
                'card',
                'hutang',
                'kembalian',
                'catatan',
                'status',
            ]);

            $table->bigInteger('amount_paid');
            $table->bigInteger('change');
            $table->enum('payment_method', ['cash', 'qris', 'transfer'])->default('cash');
        });
    }
};