{{-- <aside 
    :class="sidebarOpen ? 'w-64 overflow-hidden' : 'w-20 overflow-visible'" 
    class="z-30 flex flex-col transition-all duration-300 ease-in-out h-screen sticky top-0 bg-[#081a1e] text-slate-150 shadow-4xl"> --}}
<aside 
    :class="sidebarOpen ? 'w-64' : 'w-20'" 
    class="z-30 flex flex-col transition-all duration-300 ease-in-out h-screen sticky top-0 bg-[#081a1e] text-slate-150 shadow-4xl overflow-x-visible">
    
    {{-- Logo / Brand Area --}}
    <div class="px-6 h-20 flex items-center border-b border-slate-800/30 bg-[#051316]/40 flex-shrink-0 select-none">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0 border border-emerald-500/30">
                <i class="ri-pulse-line text-lg"></i>
            </div>
            <div x-show="sidebarOpen" class="overflow-hidden whitespace-nowrap">    
                <h1 class="font-bold text-xl tracking-wide text-green-400 leading-none mb-1">FlowPOS</h1>   
                <p class="text-[12px] text-green-100 font-bold uppercase tracking-wider">Busur Group</p>
            </div>
        </div>
    </div>

    {{-- Navigation Menu --}}
<nav :class="sidebarOpen ? 'overflow-y-auto' : 'overflow-visible'" class="flex-1 py-4 no-scrollbar">    
    
    {{-- DASHBOARD --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <a href="/dashboard" class="menu-parent flex items-center gap-3 {{ request()->is('dashboard*') ? 'submenu-active' : '' }}">
            <i class="ri-grid-fill text-lg flex-shrink-0"></i>
            <span x-show="sidebarOpen" class="text-sm">Dashboard</span>
        </a>
        {{-- <div x-show="!sidebarOpen && hovered" x-transition class="absolute left-24 top-1/2 -translate-y-1/2 bg-[#0b2428] border border-slate-800 text-emerald-400 text-xs rounded-lg py-2 px-3 shadow-xl z-50 whitespace-nowrap font-semibold pointer-events-auto">
            Dashboard
        </div> --}}
    </div>

    {{-- ==================== TRANSAKSI ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">Transaksi</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('transaksi') }" class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-exchange-funds-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-sm">Transaksi</span>
            </div>
            <i id="icon-transaksi" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        {{-- Normal Dropdown (Sidebar Buka) --}}
        <div id="menu-transaksi" class="menu-content" x-show="sidebarOpen">
            <a href="/pesanan-jasa" class="submenu {{ request()->is('pesanan-jasa*') ? 'submenu-active':'' }}"><i class="ri-customer-service-2-line mr-2.5 text-sm opacity-70"></i> Pesanan Jasa</a>
            <a href="/pesanan-barang" class="submenu {{ request()->is('pesanan-barang*') ? 'submenu-active':'' }}"><i class="ri-shopping-bag-3-line mr-2.5 text-sm opacity-70"></i> Pesanan Barang</a>
            <a href="/riwayat-pesanan" class="submenu {{ request()->is('riwayat-pesanan*') ? 'submenu-active':'' }}"><i class="ri-history-line mr-2.5 text-sm opacity-70"></i> Riwayat Pesanan</a>
            <a href="/pembatalan-pesanan" class="submenu {{ request()->is('pembatalan-pesanan*') ? 'submenu-active':'' }}"><i class="ri-close-circle-line mr-2.5 text-sm opacity-70"></i> Pembatalan Pesanan</a>
            <a href="/nota" class="submenu {{ request()->is('nota*') ? 'submenu-active':'' }}"><i class="ri-file-text-line mr-2.5 text-sm opacity-70"></i> Kasir</a>
            <a href="/riwayat-nota" class="submenu {{ request()->is('riwayat-nota*') ? 'submenu-active':'' }}"><i class="ri-file-list-3-line mr-2.5 text-sm opacity-70"></i> Riwayat Nota</a>
            <a href="/tutup-shift" class="submenu {{ request()->is('tutup-shift*') ? 'submenu-active':'' }}"><i class="ri-shut-down-line mr-2.5 text-sm opacity-70"></i> Tutup Shift</a>
        </div>

        {{-- Floating Flyout Dropdown (Sidebar Tutup) --}}
        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">Transaksi</div>
            <a href="/pesanan-jasa" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-customer-service-2-line opacity-70"></i> Pesanan Jasa</a>
            <a href="/pesanan-barang" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-shopping-bag-3-line opacity-70"></i> Pesanan Barang</a>
            <a href="/riwayat-pesanan" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-history-line opacity-70"></i> Riwayat Pesanan</a>
            <a href="/pembatalan-pesanan" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-close-circle-line opacity-70"></i> Pembatalan Pesanan</a>
            <a href="/nota" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-file-text-line opacity-70"></i> Nota</a>
            <a href="/riwayat-nota" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-file-list-3-line opacity-70"></i> Riwayat Nota</a>
            <a href="/tutup-shift" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-shut-down-line opacity-70"></i> Tutup Shift</a>
        </div>
    </div>

    {{-- ==================== MASTER ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">Master</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('master') }" class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-database-2-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-sm">Master</span>
            </div>
            <i id="icon-master" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        <div id="menu-master" class="menu-content" x-show="sidebarOpen">
            <a href="{{ route('products.index') }}" class="submenu {{ request()->is('produk') ? 'submenu-active':'' }}"><i class="ri-box-3-line mr-2.5 text-sm opacity-70"></i> Produk</a>
            <a href="/import-produk" class="submenu {{ request()->is('import-produk*') ? 'submenu-active':'' }}"><i class="ri-file-excel-line mr-2.5 text-sm opacity-70"></i> Import Produk</a>
            <a href="/kategori" class="submenu {{ request()->is('kategori*') ? 'submenu-active':'' }}"><i class="ri-price-tag-3-line mr-2.5 text-sm opacity-70"></i> Kategori</a>
            <a href="{{ route('suppliers.index') }}" class="submenu {{ request()->is('supplier*') ? 'submenu-active':'' }}"><i class="ri-truck-line mr-2.5 text-sm opacity-70"></i> Supplier</a>

            {{-- <a href="{{ route('suppliers.index') }}" 
            class="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-all duration-200 
            {{ request()->routeIs('suppliers.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <i class="ri-store-2-line text-lg {{ request()->routeIs('suppliers.*') ? 'text-green-600' : 'text-slate-400' }}"></i>
                <span>Supplier</span>
            </a> --}}

            <a href="{{ route('customers.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('customers.*') ? 'bg-green-800 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-200' }}"><i class="ri-user-smile-line mr-2.5 text-sm opacity-70"></i> Customer</a>
            <a href="/user" class="submenu {{ request()->is('user*') ? 'submenu-active':'' }}"><i class="ri-user-settings-line mr-2.5 text-sm opacity-70"></i> User</a>
        </div>

        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">Master</div>
            <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-box-3-line opacity-70"></i> Produk</a>
            <a href="/import-produk" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-file-excel-line opacity-70"></i> Import Produk</a>
            
            <a href="/supplier" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-truck-line opacity-70"></i> Supplier</a>
            <a href="/customer" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-user-smile-line opacity-70"></i> Customer</a>
            <a href="/user" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-user-settings-line opacity-70"></i> User</a>
        </div>
    </div>

    {{-- ==================== INVENTORY ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">Inventory</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('inventory') }" class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-archive-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-sm">Inventory</span>
            </div>
            <i id="icon-inventory" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        <div id="menu-inventory" class="menu-content" x-show="sidebarOpen">
            <a href="/po" class="submenu {{ request()->is('po*') ? 'submenu-active':'' }}"><i class="ri-file-list-line mr-2.5 text-sm opacity-70"></i> PO</a>
            <a href="/penerimaan-barang" class="submenu {{ request()->is('penerimaan-barang*') ? 'submenu-active':'' }}"><i class="ri-inbox-archive-line mr-2.5 text-sm opacity-70"></i> Penerimaan Barang</a>
            <a href="/kartu-stok" class="submenu {{ request()->is('kartu-stok*') ? 'submenu-active':'' }}"><i class="ri-article-line mr-2.5 text-sm opacity-70"></i> Kartu Stok</a>
            <a href="/retur-barang" class="submenu {{ request()->is('retur-barang*') ? 'submenu-active':'' }}"><i class="ri-arrow-go-back-line mr-2.5 text-sm opacity-70"></i> Retur Barang</a>
            <a href="/stok-opname" class="submenu {{ request()->is('stok-opname*') ? 'submenu-active':'' }}"><i class="ri-scan-check-line mr-2.5 text-sm opacity-70"></i> Stok Opname</a>
            <a href="/penyesuaian-stok" class="submenu {{ request()->is('penyesuaian-stok*') ? 'submenu-active':'' }}"><i class="ri-equalizer-line mr-2.5 text-sm opacity-70"></i> Penyesuaian Stok</a>
        </div>

        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">Inventory</div>
            <a href="/po" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-file-list-line opacity-70"></i> PO</a>
            <a href="/penerimaan-barang" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-inbox-archive-line opacity-70"></i> Penerimaan Barang</a>
            <a href="/kartu-stok" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-article-line opacity-70"></i> Kartu Stok</a>
            <a href="/retur-barang" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-arrow-go-back-line opacity-70"></i> Retur Barang</a>
            <a href="/stok-opname" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-scan-check-line opacity-70"></i> Stok Opname</a>
            <a href="/penyesuaian-stok" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-equalizer-line opacity-70"></i> Penyesuaian Stok</a>
        </div>
    </div>

    {{-- ==================== LAPORAN ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">Laporan</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('laporan') }" class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-bar-chart-box-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-sm">Laporan</span>
            </div>
            <i id="icon-laporan" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        <div id="menu-laporan" class="menu-content" x-show="sidebarOpen">
            <a href="/laporan/penjualan-kasir" class="submenu {{ request()->is('laporan/penjualan-kasir*') ? 'submenu-active':'' }}"><i class="ri-user-star-line mr-2.5 text-sm opacity-70"></i> Penjualan Kasir</a>
            <a href="/laporan/shift" class="submenu {{ request()->is('laporan/shift*') ? 'submenu-active':'' }}"><i class="ri-time-line mr-2.5 text-sm opacity-70"></i> Laporan Shift</a>
            <a href="/laporan/laba-rugi-kotor" class="submenu {{ request()->is('laporan/laba-rugi-kotor*') ? 'submenu-active':'' }}"><i class="ri-line-chart-line mr-2.5 text-sm opacity-70"></i> Laba rugi kotor</a>
            <a href="/laporan/penjualan-produk" class="submenu {{ request()->is('laporan/penjualan-produk*') ? 'submenu-active':'' }}"><i class="ri-focus-3-line mr-2.5 text-sm opacity-70"></i> Penjualan per produk</a>
            <a href="/laporan/penjualan-pelanggan" class="submenu {{ request()->is('laporan/penjualan-pelanggan*') ? 'submenu-active':'' }}"><i class="ri-team-line mr-2.5 text-sm opacity-70"></i> Penjualan per pelanggan</a>
            <a href="/laporan/nilai-asset-stock" class="submenu {{ request()->is('laporan/nilai-asset-stock*') ? 'submenu-active':'' }}"><i class="ri-coins-line mr-2.5 text-sm opacity-70"></i> Nilai Asset Stock</a>
        </div>

        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">Laporan</div>
            <a href="/laporan/penjualan-kasir" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-user-star-line opacity-70"></i> Penjualan Kasir</a>
            <a href="/laporan/shift" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-time-line opacity-70"></i> Laporan Shift</a>
            <a href="/laporan/laba-rugi-kotor" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-line-chart-line opacity-70"></i> Laba rugi kotor</a>
            <a href="/laporan/penjualan-produk" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-focus-3-line opacity-70"></i> Penjualan per produk</a>
            <a href="/laporan/penjualan-pelanggan" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-team-line opacity-70"></i> Penjualan per pelanggan</a>
            <a href="/laporan/nilai-asset-stock" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-coins-line opacity-70"></i> Nilai Asset Stock</a>
        </div>
    </div>

    {{-- ==================== AKUNTING ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">Akunting</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('akunting') }" class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-bank-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-sm">Akunting</span>
            </div>
            <i id="icon-akunting" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        <div id="menu-akunting" class="menu-content" x-show="sidebarOpen">
            <a href="/master-coa" class="submenu {{ request()->is('master-coa*') ? 'submenu-active':'' }}"><i class="ri-node-tree mr-2.5 text-sm opacity-70"></i> Master COA</a>
            <a href="/jurnal-adjustment" class="submenu {{ request()->is('jurnal-adjustment*') ? 'submenu-active':'' }}"><i class="ri-scales-line mr-2.5 text-sm opacity-70"></i> Jurnal Adjusment</a>
            <a href="/closing-bulanan" class="submenu {{ request()->is('closing-bulanan*') ? 'submenu-active':'' }}"><i class="ri-calendar-check-line mr-2.5 text-sm opacity-70"></i> Closing bulanan</a>
            <a href="/laporan-neraca" class="submenu {{ request()->is('laporan-neraca*') ? 'submenu-active':'' }}"><i class="ri-file-list-2-line mr-2.5 text-sm opacity-70"></i> Laporan Neraca</a>
            <a href="/laporan-cash-flow" class="submenu {{ request()->is('laporan-cash-flow*') ? 'submenu-active':'' }}"><i class="ri-refund-2-line mr-2.5 text-sm opacity-70"></i> Laporan Cash Flow</a>
            <a href="/laporan-rugi-laba" class="submenu {{ request()->is('laporan-rugi-laba*') ? 'submenu-active':'' }}"><i class="ri-advertisement-line mr-2.5 text-sm opacity-70"></i> Laporan Rugi Laba</a>
        </div>

        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">Akunting</div>
            <a href="/master-coa" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-node-tree opacity-70"></i> Master COA</a>
            <a href="/jurnal-adjustment" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-scales-line opacity-70"></i> Jurnal Adjusment</a>
            <a href="/closing-bulanan" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-calendar-check-line opacity-70"></i> Closing bulanan</a>
            <a href="/laporan-neraca" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-file-list-2-line opacity-70"></i> Laporan Neraca</a>
            <a href="/laporan-cash-flow" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-refund-2-line opacity-70"></i> Laporan Cash Flow</a>
            <a href="/laporan-rugi-laba" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-advertisement-line opacity-70"></i> Laporan Rugi Laba</a>
        </div>
    </div>

    {{-- ==================== SYSTEM ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">System</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('system') }" class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-settings-4-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-sm">System</span>
            </div>
            <i id="icon-system" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        <div id="menu-system" class="menu-content" x-show="sidebarOpen">
            <a href="/backup-data" class="submenu {{ request()->is('backup-data*') ? 'submenu-active':'' }}"><i class="ri-database-line mr-2.5 text-sm opacity-70"></i> Backup Data</a>
        </div>

        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">System</div>
            <a href="/backup-data" class="flex items-center gap-3 px-4 py-2 text-xs text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-database-line opacity-70"></i> Backup Data</a>
        </div>
    </div>
</nav>

    {{-- Footer Keluar --}}
    <div class="sidebar-footer p-3 flex-shrink-0 select-none">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-semibold text-red-400 hover:bg-red-500/10 rounded-xl transition justify-start">
                <i class="ri-logout-box-r-line text-base flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="whitespace-nowrap">Keluar Aplikasi</span>
            </button>
        </form>
    </div>
</aside>    