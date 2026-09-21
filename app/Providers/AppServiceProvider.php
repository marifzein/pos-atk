<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;     

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paksa semua URL asset & route memakai HTTPS jika diakses via Cloudflare
        //  Paksa HTTPS untuk semua panggil asset & URL
        // URL::forceScheme('https');

        // 1. MENU DEVELOPER & BACKUP DB: Hanya murni Admin IT saja
        Gate::define('akses-developer', function ($user) {
            return $user->role === 'Admin';
        });

        // 2. MENU STRATEGIS & KEUANGAN (Laporan, Laba Rugi, Pengaturan Toko): Owner & Admin
        Gate::define('akses-owner-admin', function ($user) {
            return in_array($user->role, ['Owner', 'Admin']);
        });

        // 3. MENU OPERASIONAL TINGGI (Buat PO, Approval Stok, Master Data): Owner, Admin, & Supervisor
        Gate::define('akses-spv-keatas', function ($user) {
            return in_array($user->role, ['Owner', 'Admin', 'Supervisor']);
        });

        // 4. MENU TRANSAKSI POS HARI-HARI (Kasir, Supervisor, Admin, Owner bisa buka)
        Gate::define('akses-pos', function ($user) {
            return in_array($user->role, ['Owner', 'Admin', 'Supervisor', 'Kasir']);
        });

        // 4. MENU pesanan (Operator, Supervisor, Admin, Owner bisa buka)
        Gate::define('akses-pesanan', function ($user) {
            return in_array($user->role, ['Owner', 'Admin', 'Supervisor', 'Staff Barang','Staff Jasa']);
        });

        // Tambahkan Macro kustom untuk pengecekan Mobile di Request
        Request::macro('isMobile', function () {
            $userAgent = $this->userAgent();
            return (bool) preg_match('/(android|bb\d+|meego).+mobile|blackberry|iemobile|iphone|ipod|opera mini|mobi/i', $userAgent);
        });
    }
}
