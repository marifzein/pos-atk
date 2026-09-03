<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClientModule;

class ClientModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Daftar semua Controller dari folder app/Http/Controllers
        $controllers = [
            'DashboardController',
            'CoaController',
            'CustomerController',
            'DailyResetStockController',
            'DeveloperController',
            'KasirController',
            'LaporanLabaRugiController',
            'LaporanPenjualanKasirController',
            'LaporanPenjualanPelangganController',
            'LaporanPenjualanProdukController',
            'PembatalanController',
            'PenerimaanBarangController',
            'PesananBarangController',
            'PesananJasaController',
            'ProductController',
            'ProfileController',
            'PurchaseOrderController',
            'ReturBarangController',
            'SettingController',
            'ShiftController',
            'StockAdjustmentController',
            'StockCardController',
            'StockOpnameController',
            'StockValuationController',
            'SupplierController',
            'UserController',
        ];

        // Looping untuk memasukkan atau mengupdate data ke database
        foreach ($controllers as $controller) {
            ClientModule::updateOrCreate(
                ['controller_name' => $controller], // Cari berdasarkan nama controller
                ['is_active' => true]               // Set default true (aktif)
            );
        }
    }
}