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
        Schema::create('cash_expenses', function (Blueprint $table) {
            $table->id();
            
            // Relasi Toko & Kasir
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->foreignId('user_id')->comment('Kasir/Staf yang mengeluarkan uang')->constrained('users')->cascadeOnDelete();
            
            // Nomor Dokumen Unik Kas Keluar, contoh: KK-TU-260929-0001
            $table->string('no_bukti', 50)->unique();
            
            // Kategori Pengeluaran
            // operasional    : iuran sampah, listrik, konsumsi, galon
            // teknisi_subkon : biaya ongkos setting, jahit spanduk rekanan, servis mesin
            // barang_supplier: bayar COD Shopee / bayar faktur sales distributor
            // tarik_owner    : setoran brankas / prive bos di tengah shift
            // lain_lain      : pengeluaran tidak terduga lainnya
            $table->enum('kategori', [
                'operasional', 
                'teknisi_subkon', 
                'barang_supplier', 
                'tarik_owner', 
                'lain_lain'
            ])->default('operasional');
            
            // Nama Pihak yang Menerima Uang Tunai
            $table->string('penerima', 100);
            
            // Nominal Pengeluaran
            $table->decimal('nominal', 15, 2);
            
            // Relasi Fleksibel (Polymorphic Reference)
            // Jika kategori 'barang_supplier' -> reference_type: 'penerimaan_barang', reference_id: penerimaan_barang.id
            $table->string('reference_type', 50)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            
            // Catatan atau Keterangan Tambahan
            $table->text('catatan')->nullable();
            
            $table->timestamps();

            // Indexing untuk kecepatan filter audit per cabang & per shift
            $table->index(['branch_id', 'shift_id']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_expenses');
    }
};