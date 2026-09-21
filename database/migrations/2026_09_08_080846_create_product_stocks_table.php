<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'branch_id']);
        });

        // Pindahkan stok lama dari tabel `products` ke `product_stocks` cabang Pusat (1)
        $products = DB::table('products')->get();
        foreach ($products as $product) {
            DB::table('product_stocks')->insert([
                'product_id' => $product->id,
                'branch_id' => 1,
                'stock' => $product->stock ?? 0,
                'min_stock' => $product->min_stock ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
