<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS ATK')</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    
    <!-- Remixicon atau FontAwesome untuk Icon -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <style>
    body { 
        font-family: 'Figtree', sans-serif !important; 
        letter-spacing: -0.02em; 
    }
    
    /* Warna Background Terinspirasi Greeweb (Dark Teal Kedalaman Tinggi) */
    aside { 
        background: #081a1e; /* Warna gelap khas panel kontrol industri */
        color: #f1f5f9; 
        box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15); 
    }
    
    .menu-group { 
        color: #4a666a; 
        font-size: 0.725rem; 
        text-transform: uppercase; 
        letter-spacing: 0.08em; 
        font-weight: 700; 
        padding-left: 24px; 
        margin-top: 1.5rem; 
        margin-bottom: 0.5rem; 
    }
    
    .menu-parent { 
        color: #94a3b8; 
        border-radius: 10px; 
        margin: 2px 14px; 
        padding: 9px 14px; 
        font-weight: 500; 
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); 
    }
    
    .menu-parent:hover { 
        background: rgba(255, 255, 255, 0.04); 
        color: #fff; 
    }
    
    .submenu { 
        display: flex; 
        align-items: center; 
        padding: 8px 16px 8px 42px; 
        margin: 2px 14px; 
        /*color: #8fa0a4;*/ 
        /*color: #CBD5E1 ;*/ 
        color: #94A3B8  ; 
        font-size: 0.9rem; 
        font-weight:500;
        border-radius: 8px; 
        transition: all 0.2s ease; 
    }
    
    .submenu:hover { 
        background: rgba(255, 255, 255, 0.02); 
        color: #11b981; 
        padding-left: 46px; 
    }
    
    /* 💡 KUNCI FIX: Indikator Aktif Tipe Soft Tint Green ala Greeweb */
    .submenu-active { 
        background: rgba(16, 185, 129, 0.12) !important; /* Hijau super soft transparan */
        color: #34d399 !important; /* Teks Mint Green lembut tapi kontras */
        font-weight: 600; 
    }
    
    .menu-content { 
        overflow: hidden; 
        max-height: 0; 
        opacity: 0; 
        transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.2s ease; 
    }
    
    .menu-content.open { 
        max-height: 1000px; 
        opacity: 1; 
    }
    
    .rotate { 
        transform: rotate(90deg); 
        color: #34d399 !important; 
    }
    
    .sidebar-footer { 
        border-top: 1px solid rgba(255, 255, 255, 0.04); 
        background: rgba(0, 0, 0, 0.15); 
    }

    /* Mengubah warna latar belakang option saat di-hover/pilih di browser modern */
    select option:hover,
    select option:focus,
    select option:active,
    select option:checked {
        background-color: #10b981 !important; /* Warna Hijau Emerald */
        color: white !important;
    }
    

    /* 💡 KUNCI FIX: Pembasmi Scrollbar Menjengkelkan */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased" x-data="{ sidebarOpen: true }">
    <div class="flex h-screen overflow-hidden">
        {{-- Sidebar --}}
        @include('layouts.partials.sidebar')

        {{-- Content Area --}}
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out h-screen overflow-hidden">
            
            {{-- Header / Topbar --}}
            @include('layouts.partials.topbar')

            {{-- Main Scrollable Content --}}
            <main class="flex-1 overflow-y-auto bg-slate-50/50">
                <div class="p-6 md:p-8 pb-24">
                    <!-- Bungkus konten utama dalam card transparan halus -->
                    <div class="animate-fade-in">
                        @yield('content')
                    </div>
                </div>
            </main>

            <footer class="h-10 bg-white border-t border-slate-200 flex items-center justify-between px-6 text-xs text-slate-500 z-30 select-none">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span>
                    <span>flowpos.com</span>
                </div>
                <div class="hidden md:flex items-center gap-4 text-slate-400">
                    <span>Kasir: <strong class="text-slate-700">{{ Auth::user()->name }}</strong></span>
                    <span>•</span>
                    <span>Modul: <strong class="text-indigo-600 font-medium">FlowPOS ATK v.1.0</strong></span>
                </div>
                <div>@2026 Powered by <strong class="text-slate-700">Zezdev</strong>Style</div>
            </footer>
        </div>
    </div>

    
    <script>
    const menus = ['kasir', 'master', 'inventory', 'laporan', 'system', 'akunting', 'transaksi'];

    function toggleMenu(name){
        menus.forEach(function(item){
            const menu = document.getElementById('menu-'+item);
            const icon = document.getElementById('icon-'+item);
            
            if (menu && icon) { 
                if (item === name) {
                    if (menu.classList.contains('open')) {
                        menu.classList.remove('open');
                        icon.classList.remove('rotate');
                        localStorage.removeItem('activeMenu');
                    } else {
                        menu.classList.add('open');
                        icon.classList.add('rotate');
                        localStorage.setItem('activeMenu', item);
                    }
                } else {
                    // Otomatis menutup menu lain yang tidak diklik
                    menu.classList.remove('open');
                    icon.classList.remove('rotate');
                }
            }
        });
    }

    // PEMBASMI ERROR RELOAD: Deteksi gabungan URL Aktif + LocalStorage
    document.addEventListener('DOMContentLoaded', function(){
        let active = localStorage.getItem('activeMenu');
        let openedByUrl = false;

        // Langkah 1: Cek berdasarkan URL aktif saat ini (Pendekatan POS Minimarket)
        const menuButtons = document.querySelectorAll('button[data-url-active="true"]');
        if (menuButtons.length > 0) {
            menuButtons.forEach(button => {
                const menuName = button.getAttribute('data-menu-name');
                if (menuName) {
                    document.getElementById('menu-' + menuName)?.classList.add('open');
                    document.getElementById('icon-' + menuName)?.classList.add('rotate');
                    localStorage.setItem('activeMenu', menuName); // Perbarui storage agar sinkron
                    openedByUrl = true;
                }
            });
        }

        // Langkah 2: Jika URL tidak spesifik (misal di halaman luar), gunakan cadangan LocalStorage
        if (!openedByUrl && active) {
            document.getElementById('menu-' + active)?.classList.add('open');
            document.getElementById('icon-' + active)?.classList.add('rotate');
        }
    });
    </script>
    @stack('scripts')
</body>
</html>