<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PesananJasaController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\PesananBarangController;


use App\Http\Controllers\LaporanLabaRugiController;
use App\Http\Controllers\LaporanPenjualanProdukController;
use App\Http\Controllers\LaporanPenjualanKasirController;
use App\Http\Controllers\LaporanPenjualanPelangganController;
use App\Http\Controllers\Laporan\StockValuationController;
use App\Http\Controllers\ShiftController;

use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PenerimaanBarangController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\DailyResetStockController;
use App\Http\Controllers\ReturBarangController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StockCardController;




Route::get('/', function () {
    // Jika user sudah login, langsung lempar ke dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    
    // Jika belum login, langsung lempar ke halaman form login kustom kamu
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
Route::middleware(['auth', \App\Http\Middleware\CheckCommissionScheme::class])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Route Menampilkan Form Change Password
    Route::get('/password/change', [ProfileController::class, 'changePassword'])->name('password.change');
    // Route Eksekusi Update Password
    Route::put('/password/update', [ProfileController::class, 'updatePassword'])->name('password.password-update');


    // Pesanan Jasa
    Route::get('/pesanan-jasa', [PesananJasaController::class, 'index'])->name('pesanan-jasa.index');
    Route::post('/api/pesanan-jasa', [PesananJasaController::class, 'store'])->name('pesanan-jasa.store');
    Route::get('/pesanan-jasa/riwayat', [PesananJasaController::class, 'history'])->name('pesanan-jasa.history');
    Route::get('/pesanan-jasa/{order}', [PesananJasaController::class, 'show'])->name('pesanan-jasa.show');
    Route::post('/pesanan-jasa/{id}/batal', [PesananJasaController::class, 'cancelOrder'])->name('pesanan-jasa.batal');
    
    // Route Web Views Pesanan Barang
    Route::get('/pesanan-barang', [PesananBarangController::class, 'index'])->name('pesanan-barang.index');
    Route::post('/api/pesanan-barang', [PesananBarangController::class, 'store'])->name('api.pesanan-barang.store');
    Route::get('/pesanan-barang/history', [PesananBarangController::class, 'history'])->name('pesanan-barang.history');
    Route::get('/pesanan-barang/{order}', [PesananBarangController::class, 'show'])->name('pesanan-barang.show');
    Route::post('/pesanan-barang/{id}/batal', [PesananBarangController::class, 'cancelOrder'])->name('pesanan-barang.cancel');

    

    // INVENTORY
        // Purchase Order (PO) & Cetak PDF
        Route::resource('purchasing', PurchaseOrderController::class)->except(['destroy']);
        Route::get('purchasing/{purchasing}/print-pdf', [PurchaseOrderController::class, 'printPdf'])->name('purchasing.print-pdf');

        // Kartu Stok
        Route::get('stock-cards', [StockCardController::class, 'index'])->name('stock-cards.index');
        Route::get('stock-cards/{product}', [StockCardController::class, 'show'])->name('stock-cards.show');


        // Penerimaan Barang
        Route::get('/penerimaan-barang', [PenerimaanBarangController::class, 'index'])->name('penerimaan.index');
        Route::get('/penerimaan-barang/create', [PenerimaanBarangController::class, 'create'])->name('penerimaan.create');
        Route::post('/penerimaan-barang', [PenerimaanBarangController::class, 'store'])->name('penerimaan.store');
        Route::get('/api/penerimaan/search-products', [PenerimaanBarangController::class, 'searchProducts']);
        Route::get('/penerimaan-barang/{id}', [PenerimaanBarangController::class, 'show'])->name('penerimaan.show');
        Route::get('/penerimaan-barang/{id}/print', [PenerimaanBarangController::class, 'print'])->name('penerimaan.print');

        // Stok Opname
        Route::get('/stock-opname', [StockOpnameController::class, 'index']);
        Route::post('/stock-opname/start', [StockOpnameController::class, 'start']);
        Route::get('/stock-opname/{stockOpname}', [StockOpnameController::class, 'show']);
        Route::post('/stock-opname/{stockOpname}', [StockOpnameController::class, 'store']);
        Route::post('/stock-opname/{stockOpname}/finish', [StockOpnameController::class, 'finish']);
        Route::get('/stock-opname/{stockOpname}/print', [StockOpnameController::class, 'print'])->name('stock-opname.print');

         // Retur Barang
        Route::get('/api/retur/search-products', [ReturBarangController::class, 'searchProducts'])->name('api.retur.search-products');
        Route::resource('retur', ReturBarangController::class)->only(['index', 'create', 'store', 'show']);
        Route::get('/retur/{id}/print', [ReturBarangController::class, 'print'])->name('retur.print');

        
        // Fitur Reset Stok Harian Resto (Langsung Posted) Stck opname harian otomatis reset 0
        Route::prefix('inventory/daily-reset')->name('daily-reset.')->group(function () {
            Route::get('/', [DailyResetStockController::class, 'index'])->name('index');
            Route::post('/', [DailyResetStockController::class, 'store'])->name('store');
        }); 


        // Penyesuaian Stok (Stock Adjustment)
        Route::resource('stock-adjustments', StockAdjustmentController::class)->except(['show', 'destroy']);
        Route::post('/stock-adjustments/{stockAdjustment}/post', [StockAdjustmentController::class, 'post'])->name('stock-adjustments.post');
        Route::get('/stock-adjustments/{stockAdjustment}/print-pdf', 
        [StockAdjustmentController::class, 'printPdf'])->name('stock-adjustments.print-pdf');

        



    // customer
    Route::resource('customers', CustomerController::class);
    // Customer API / AJAX
    Route::post('/api/customers', [CustomerController::class, 'storeApi'])->name('api.customers.store');


    // supplier
    Route::resource('suppliers', SupplierController::class);

    // product
    Route::resource('products', ProductController::class);
    Route::get('api/products/search', [ProductController::class, 'search']);
    Route::get('api/products/search-barang', [ProductController::class, 'search_barang']);
    Route::get('api/products/search_jasa', [ProductController::class, 'search_jasa']);
    

    //users
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    
    // kasir/POS
    Route::middleware(['check.shift'])->group(function () {
        Route::get('/kasir', [KasirController::class, 'index'])->name('kasir.index');
        Route::get('/kasir/create', [KasirController::class, 'create'])->name('kasir.create');
        Route::post('/kasir/store', [KasirController::class, 'storeTransaction']);

        Route::get('/kasir/api/orders', [KasirController::class, 'apiOrders'])->name('kasir.api.orders');
    });

    // 💡 2. RUTE KHUSUS UNTUK PROSES ISI uang MODAL AWAL kasir (DI LUAR PROTEKSI SHIFT)
        Route::get('/kasir/open-shift', [App\Http\Controllers\ShiftController::class, 'showOpenForm'])->name('kasir.open-shift');
        Route::post('/kasir/open-shift', [App\Http\Controllers\ShiftController::class, 'storeOpenShift'])->name('kasir.store-shift');

        // 💡 RUTE close sfhift:
        Route::get('/kasir/close-shift', [App\Http\Controllers\ShiftController::class, 'showCloseForm'])->name('kasir.close-shift');
        Route::post('/kasir/close-shift', [App\Http\Controllers\ShiftController::class, 'storeCloseShift'])->name('kasir.store-close');

        // Jalur menu Laporan Toko -> Laporan Shift
        Route::get('/laporan/shift', [\App\Http\Controllers\ShiftController::class, 'index'])->name('laporan.shift.index');
        Route::get('/laporan/shift/{id}', [\App\Http\Controllers\ShiftController::class, 'show'])->name('laporan.shift.show');


        // Transaksi & Print
        // Route::get('/transactions', [KasirController::class, 'index'])->name('transactions.index');;
        Route::get('/kasir/history', [KasirController::class, 'history'])->name('kasir.history');
        Route::get('/kasir/{id}', [KasirController::class, 'show'])->name('kasir.show');
        Route::get('/kasir/{id}/print', [KasirController::class, 'print'])->name('kasir.print');


        // Laporan Penjualan Kasir
        Route::get('/laporan/penjualan-kasir', [LaporanPenjualanKasirController::class, 'index'])->name('laporan.penjualan-kasir');
        
        // Laporan Penjualan per produk
        Route::get('/laporan/penjualan-produk', [LaporanPenjualanProdukController::class, 'index']);

        // Laporan Penjualan per pelanggan
        Route::get('/laporan/penjualan-pelanggan', [LaporanPenjualanPelangganController::class, 'index']);
        
        // Laporan shift
        Route::get('/laporan/shift', [\App\Http\Controllers\ShiftController::class, 'index'])->name('laporan.shift.index');
        Route::get('/laporan/shift/{id}', [\App\Http\Controllers\ShiftController::class, 'show'])->name('laporan.shift.show');
        

        // Laporan Rugi Laba Kotor
        Route::get('/laporan/laba-rugi-kotor', [LaporanLabaRugiController::class, 'index'])->name('laporan.laba-rugi');
        Route::get('/laporan/laba-rugi/excel', [LaporanLabaRugiController::class, 'exportExcel'])->name('laporan.laba-rugi.excel');
        Route::get('/laporan/laba-rugi/pdf', [LaporanLabaRugiController::class, 'exportPdf'])->name('laporan.laba-rugi.pdf');

        // Laporan nilai asset
        Route::get('/laporan/nilai-aset-stok', [StockValuationController::class, 'index'])->name('laporan.nilai-aset');
        
    /*
    |--------------------------------------------------------------------------
    | 4. GRUP TEKNIS DEVELOPER (Murni Hanya Admin IT)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['can:akses-developer'])->group(function () {
        // Developer Tool
        Route::prefix('developer')->name('developer.')->group(function () {
            Route::get('/', [DeveloperController::class, 'index'])->name('index');
            Route::post('/reset-transaksi', [DeveloperController::class, 'resetTransaksi'])->name('reset.transaksi');
            Route::post('/reset-master', [DeveloperController::class, 'resetMaster'])->name('reset.master');
            Route::post('/reset-footer', [DeveloperController::class, 'resetFooter'])->name('reset.footer');    
            Route::post('/seed', [DeveloperController::class, 'seedDemo'])->name('seed');

            // MANAGEMEN INTEGRASI AKSES MODUL CLIENT
            Route::get('/modules', [\App\Http\Controllers\DeveloperController::class, 'modulesIndex'])->name('modules.index');
            Route::post('/modules/update', [\App\Http\Controllers\DeveloperController::class, 'modulesUpdate'])->name('modules.update');
        });
        
        // Pengaturan Profil Toko
        Route::get('/system/setting', [SettingController::class, 'index'])->name('setting.index');
        Route::put('/system/setting', [SettingController::class, 'update'])->name('setting.update');    
        
    });

    });

require __DIR__.'/auth.php';