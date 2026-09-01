<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TataKas')</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">


    {{-- <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet"> --}}
    
    <!-- Remixicon atau FontAwesome untuk Icon -->
    {{-- <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/> --}}
    <link href="{{ asset('css/remixicon/remixicon.css') }}" rel="stylesheet" />
    <link href="{{ asset('css/sidebar.css') }}" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
   
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