<aside 
    :class="sidebarOpen ? 'w-64' : 'w-20'" 
    class="z-30 flex flex-col transition-all duration-300 ease-in-out h-screen sticky top-0 bg-[#081a1e] text-slate-150 shadow-4xl overflow-x-visible">
    
    {{-- Logo / Brand Area --}}
    <div class="px-6 h-20 flex items-center border-b border-slate-800/30 bg-[#051316]/40 flex-shrink-0 select-none">
        <div class="flex items-center gap-3">
            {{-- <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center flex-shrink-0 border border-emerald-500/30">
                <i class="ri-pulse-line text-lg"></i>
            </div> --}}
            <div x-show="sidebarOpen" class="overflow-hidden whitespace-nowrap">    
                {{-- <h1 class="font-bold text-xl tracking-wide text-green-500 leading-none mb-1">FlowPOS</h1>    --}}
                <img src="{{ asset('images/tatakas.png') }}" alt="TATAKAS Logo" class="h-5 object-contain">
                <p class=" text-green-200 ">Busur Group</p>
            </div>
        </div>
    </div>

    {{-- Navigation Menu --}}
<nav :class="sidebarOpen ? 'overflow-y-auto' : 'overflow-visible'" class="flex-1 py-4 no-scrollbar">    
    
    {{-- DASHBOARD --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <a href="{{ url('/dashboard') }}" class="menu-parent flex items-center gap-3 {{ request()->is('dashboard*') ? 'submenu-active' : '' }}">
            <i class="ri-grid-fill text-lg flex-shrink-0"></i>
            <span x-show="sidebarOpen" class="text-base">Dashboard</span>
        </a>
        {{-- <div x-show="!sidebarOpen && hovered" x-transition class="absolute left-24 top-1/2 -translate-y-1/2 bg-[#0b2428] border border-slate-800 text-emerald-400 text-xs rounded-lg py-2 px-3 shadow-xl z-50 whitespace-nowrap font-semibold pointer-events-auto">
            Dashboard
        </div> --}}
    </div>

    {{-- ==================== TRANSAKSI ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">Transaksi</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('transaksi') }" 
        data-menu-name="transaksi" 
        data-url-active="{{ request()->is('pesanan-jasa*', 'pesanan-barang*', 'riwayat-pesanan*', 'pembatalan-pesanan*', 'nota*', 'riwayat-nota*', 'tutup-shift*') ? 'true' : 'false' }}"

        class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-exchange-funds-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-base">Transaksi</span>
            </div>
            <i id="icon-transaksi" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        {{-- Normal Dropdown (Sidebar Buka) --}}
        <div id="menu-transaksi" class="menu-content" x-show="sidebarOpen">
            <a href="{{ url('/pesanan-jasa') }}" class="submenu {{ request()->is('pesanan-jasa') ? 'submenu-active':'' }}"><i class="ri-customer-service-2-line text-sm mr-2.5 opacity-90"></i> Pesanan Jasa</a>
            <a href="{{ url('/pesanan-barang') }}" class="submenu {{ request()->is('pesanan-barang*') ? 'submenu-active':'' }}"><i class="ri-shopping-bag-3-line text-sm mr-2.5 opacity-90"></i> Pesanan Barang</a>
            <a href="{{ url('/pesanan-jasa/riwayat') }}" class="submenu {{ request()->is('pesanan-jasa/riwayat*') ? 'submenu-active':'' }}"><i class="ri-history-line text-sm mr-2.5 opacity-90"></i> Riwayat Pesanan</a>
            
            <a href="{{ url('/kasir') }}" class="submenu {{ request()->is('kasir') ? 'submenu-active':'' }}"><i class="ri-file-text-line text-sm mr-2.5 opacity-90"></i> Kasir</a>
            <a href="{{ url('/kasir/history') }}" class="submenu {{ request()->is('kasir/history') ? 'submenu-active':'' }}"><i class="ri-file-list-3-line text-sm mr-2.5 opacity-90"></i> Riwayat Nota</a>
            <a href="{{ url('/kasir/close-shift') }}" class="submenu {{ request()->is('tutup-shift*') ? 'submenu-active':'' }}"><i class="ri-shut-down-line text-sm mr-2.5 opacity-90"></i> Tutup Shift</a>
        </div>

        {{-- Floating Flyout Dropdown (Sidebar Tutup) --}}
        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-sm font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">Transaksi</div>
            <a href="{{ url('/pesanan-jasa') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-customer-service-2-line opacity-90"></i> Pesanan Jasa</a>
            <a href="{{ url('/pesanan-barang') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-shopping-bag-3-line opacity-90"></i> Pesanan Barang</a>
            <a href="{{ url('/pesanan-jasa/riwayat') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-history-line opacity-90"></i> Riwayat Pesanan</a>
            <a href="{{ url('/pembatalan-pesanan') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-close-circle-line opacity-90"></i> Pembatalan Pesanan</a>
            <a href="{{ url('/kasir') }} " class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-file-text-line opacity-90"></i> Nota</a>
            <a href="{{ url('/kasir/history') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-file-list-3-line opacity-90"></i> Riwayat Nota</a>
            <a href="{{ url('/tutup-shift') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-shut-down-line opacity-90"></i> Tutup Shift</a>
        </div>
    </div>

    {{-- ==================== MASTER ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">Master</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('master') }" 
        data-menu-name="master"
        data-url-active="{{ request()->is('products*', 'import-produk*', 'supplier*', 'customer*', 'user*') ? 'true' : 'false' }}"
        class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-database-2-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-sm">Master</span>
            </div>
            <i id="icon-master" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        <div id="menu-master" class="menu-content" x-show="sidebarOpen">
            <a href="{{ route('products.index') }}" class="submenu {{ request()->is('products*') ? 'submenu-active':'' }}"><i class="ri-box-3-line mr-2.5 text-sm mr-2.5 opacity-90"></i> Produk</a>
            {{-- <a href="{{ url('/import-produk') }}" class="submenu {{ request()->is('import-produk*') ? 'submenu-active':'' }}"><i class="ri-file-excel-line mr-2.5 text-sm opacity-90"></i> Import Produk</a> --}}
            {{-- <a href="/kategori" class="submenu {{ request()->is('kategori*') ? 'submenu-active':'' }}"><i class="ri-price-tag-3-line mr-2.5 text-sm opacity-90"></i> Kategori</a> --}}
            <a href="{{ route('suppliers.index') }}" class="submenu {{ request()->is('supplier*') ? 'submenu-active':'' }}"><i class="ri-truck-line mr-2.5 text-sm opacity-90"></i> Supplier</a>
           
            <a href="{{ route('customers.index') }}" class="submenu {{ request()->is('customer*') ? 'submenu-active':'' }}"><i class="ri-user-shared-line mr-2.5 text-sm opacity-90"></i> Customer</a>

            <a href="{{ route('users.index') }}" class="submenu {{ request()->is('user*') ? 'submenu-active':'' }}"><i class="ri-user-fill mr-2.5 text-sm opacity-90"></i> User</a>
        </div>

        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">Master</div>

            

            <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-box-3-line opacity-90"></i> Produk</a>
            {{-- <a href="{{ url('/import-produk') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-file-excel-line opacity-90"></i> Import Produk</a> --}}
            
            <a href="{{ url('/supplier') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-truck-line opacity-90"></i> Supplier</a>
            <a href="{{ url('/customer') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-user-shared-line opacity-90"></i> Customer</a>
            <a href="{{ url('/user') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-user-fill opacity-90"></i> User</a>
        </div>
    </div>

    {{-- ==================== INVENTORY ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">Inventory</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('inventory') }" 
        data-menu-name="inventory"
        
        class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-archive-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-sm">Inventory</span>
            </div>
            <i id="icon-inventory" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        <div id="menu-inventory" class="menu-content" x-show="sidebarOpen">
            <a href="{{ url('/po') }}" class="submenu {{ request()->is('po*') ? 'submenu-active':'' }}"><i class="ri-file-list-line mr-2.5 text-sm opacity-90"></i> PO</a>
            <a href="{{ url('/penerimaan-barang') }}" class="submenu {{ request()->is('penerimaan-barang*') ? 'submenu-active':'' }}"><i class="ri-inbox-archive-line mr-2.5 text-sm opacity-90"></i> Penerimaan Barang</a>
            <a href="{{ url('/kartu-stok') }}" class="submenu {{ request()->is('kartu-stok*') ? 'submenu-active':'' }}"><i class="ri-article-line mr-2.5 text-sm opacity-90"></i> Kartu Stok</a>
            <a href="{{ url('/retur-barang') }}" class="submenu {{ request()->is('retur-barang*') ? 'submenu-active':'' }}"><i class="ri-arrow-go-back-line mr-2.5 text-sm opacity-90"></i> Retur Barang</a>
            <a href="{{ url('/stok-opname') }}" class="submenu {{ request()->is('stok-opname*') ? 'submenu-active':'' }}"><i class="ri-archive-line mr-2.5 text-sm opacity-90"></i> Stok Opname</a>
            <a href="{{ url('/penyesuaian-stok') }}" class="submenu {{ request()->is('penyesuaian-stok*') ? 'submenu-active':'' }}"><i class="ri-equalizer-line mr-2.5 text-sm opacity-90"></i> Penyesuaian Stok</a>
        </div>

        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">Inventory</div>
            <a href="{{ url('/po') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-file-list-line opacity-90"></i> PO</a>
            <a href="{{ url('/penerimaan-barang') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-inbox-archive-line opacity-90"></i> Penerimaan Barang</a>
            <a href="{{ url('/kartu-stok') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-article-line opacity-90"></i> Kartu Stok</a>
            <a href="{{ url('/retur-barang') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-arrow-go-back-line opacity-90"></i> Retur Barang</a>
            <a href="{{ url('/stok-opname') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-archive-line opacity-90"></i> Stok Opname</a>
            <a href="{{ url('/penyesuaian-stok') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-equalizer-line opacity-90"></i> Penyesuaian Stok</a>
        </div>
    </div>

    {{-- ==================== LAPORAN ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">Laporan</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('laporan') }" 
        data-menu-name="laporan"

        class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-bar-chart-box-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-sm">Laporan</span>
            </div>
            <i id="icon-laporan" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        <div id="menu-laporan" class="menu-content" x-show="sidebarOpen">
            <a href="{{ url('/laporan/penjualan-kasir') }}" class="submenu {{ request()->is('laporan/penjualan-kasir*') ? 'submenu-active':'' }}"><i class="ri-user-star-line mr-2.5 text-sm opacity-90"></i> Penjualan Kasir</a>
            <a href="{{ url('/laporan/shift') }}" class="submenu {{ request()->is('laporan/shift*') ? 'submenu-active':'' }}"><i class="ri-time-line mr-2.5 text-sm opacity-90"></i> Laporan Shift</a>
            <a href="{{ url('/laporan/laba-rugi-kotor') }}" class="submenu {{ request()->is('laporan/laba-rugi-kotor*') ? 'submenu-active':'' }}"><i class="ri-line-chart-line mr-2.5 text-sm opacity-90"></i> Laba rugi kotor</a>
            <a href="{{ url('/laporan/penjualan-produk') }}" class="submenu {{ request()->is('laporan/penjualan-produk*') ? 'submenu-active':'' }}"><i class="ri-focus-3-line mr-2.5 text-sm opacity-90"></i> Penjualan per produk</a>
            <a href="{{ url('/laporan/penjualan-pelanggan') }}" class="submenu {{ request()->is('laporan/penjualan-pelanggan*') ? 'submenu-active':'' }}"><i class="ri-team-line mr-2.5 text-sm opacity-90"></i> Penjualan per pelanggan</a>
            <a href="{{ url('/laporan/nilai-aset-stok') }}" class="submenu {{ request()->is('laporan/nilai-aset-stok*') ? 'submenu-active':'' }}"><i class="ri-coins-line mr-2.5 text-sm opacity-90"></i> Nilai Asset Stock</a>
        </div>

        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">Laporan</div>
            <a href="{{ url('/laporan/penjualan-kasir') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-user-star-line opacity-90"></i> Penjualan Kasir</a>
            <a href="{{ url('/laporan/shift') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-time-line opacity-90"></i> Laporan Shift</a>
            <a href="{{ url('/laporan/laba-rugi-kotor') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-line-chart-line opacity-90"></i> Laba rugi kotor</a>
            <a href="{{ url('/laporan/penjualan-produk') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-focus-3-line opacity-90"></i> Penjualan per produk</a>
            <a href="{{ url('/laporan/penjualan-pelanggan') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-team-line opacity-90"></i> Penjualan per pelanggan</a>
            <a href="{{ url('/laporan/nilai-aset-stok') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-coins-line opacity-90"></i> Nilai Asset Stock</a>
        </div>
    </div>

    {{-- ==================== AKUNTING ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">Akunting</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('akunting') }" 
        data-menu-name="akunting"
        class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-bank-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-sm">Akunting</span>
            </div>
            <i id="icon-akunting" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        <div id="menu-akunting" class="menu-content" x-show="sidebarOpen">
            <a href="{{ url('/master-coa') }}" class="submenu {{ request()->is('master-coa*') ? 'submenu-active':'' }}"><i class="ri-node-tree mr-2.5 text-sm opacity-90"></i> Master COA</a>
            <a href="{{ url('/jurnal-adjustment') }}" class="submenu {{ request()->is('jurnal-adjustment*') ? 'submenu-active':'' }}"><i class="ri-scales-line mr-2.5 text-sm opacity-90"></i> Jurnal Adjusment</a>
            <a href="{{ url('/closing-bulanan') }}" class="submenu {{ request()->is('closing-bulanan*') ? 'submenu-active':'' }}"><i class="ri-calendar-check-line mr-2.5 text-sm opacity-90"></i> Closing bulanan</a>
            <a href="{{ url('/laporan-neraca') }}" class="submenu {{ request()->is('laporan-neraca*') ? 'submenu-active':'' }}"><i class="ri-file-list-2-line mr-2.5 text-sm opacity-90"></i> Laporan Neraca</a>
            <a href="{{ url('/laporan-cash-flow') }}" class="submenu {{ request()->is('laporan-cash-flow*') ? 'submenu-active':'' }}"><i class="ri-refund-2-line mr-2.5 text-sm opacity-90"></i> Laporan Cash Flow</a>
            <a href="{{ url('/laporan-rugi-laba') }}" class="submenu {{ request()->is('laporan-rugi-laba*') ? 'submenu-active':'' }}"><i class="ri-advertisement-line mr-2.5 text-sm opacity-90"></i> Laporan Rugi Laba</a>
        </div>

        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">Akunting</div>
            <a href="{{ url('/master-coa') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-node-tree opacity-90"></i> Master COA</a>
            <a href="{{ url('/jurnal-adjustment') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-scales-line opacity-90"></i> Jurnal Adjusment</a>
            <a href="{{ url('/closing-bulanan') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-calendar-check-line opacity-90"></i> Closing bulanan</a>
            <a href="{{ url('/laporan-neraca') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-file-list-2-line opacity-90"></i> Laporan Neraca</a>
            <a href="{{ url('/laporan-cash-flow') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-refund-2-line opacity-90"></i> Laporan Cash Flow</a>
            <a href="{{ url('/laporan-rugi-laba') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-advertisement-line opacity-90"></i> Laporan Rugi Laba</a>
        </div>
    </div>

    {{-- ==================== SYSTEM ==================== --}}
    {{-- <div x-show="sidebarOpen" class="menu-group">System</div> --}}
    <div class="relative group mb-1" x-data="{ hovered: false }" @mouseenter="hovered = true" @mouseleave="hovered = false">
        <button type="button" @click="if (sidebarOpen) { toggleMenu('system') }" 
        data-menu-name="system"

        class="menu-parent w-full flex items-center justify-between focus:outline-none text-left">
            <div class="flex items-center gap-3">
                <i class="ri-settings-4-line text-lg flex-shrink-0"></i>
                <span x-show="sidebarOpen" class="text-sm">System</span>
            </div>
            <i id="icon-system" x-show="sidebarOpen" class="ri-arrow-right-s-line text-xs transition-transform"></i>
        </button>

        <div id="menu-system" class="menu-content" x-show="sidebarOpen">
            @can('akses-developer')
                <a href="{{ route('setting.index') }}" class="submenu {{ request()->is('system/setting*') ? 'submenu-active':'' }}">
                    <i class="ri-settings-4-line mr-2.5 text-sm opacity-90"></i> Pengaturan Toko
                </a>
                <a href="{{ route('developer.modules.index') }}" class="submenu {{ request()->is('developer/modules*') ? 'submenu-active':'' }}">
                    <i class="ri-shield-keyhole-line mr-2.5 text-sm opacity-90"></i> Akses Modul Client
                </a>
                <a href="{{ url('/developer') }}" class="submenu {{ request()->is('developer') ? 'submenu-active':'' }}">
                    <i class="ri-code-s-slash-line mr-2.5 text-sm opacity-90"></i> Developer
                </a>
            @endcan
            <a href="{{ url('/backup-data') }}" class="submenu {{ request()->is('backup-data*') ? 'submenu-active':'' }}"><i class="ri-database-line mr-2.5 text-sm opacity-90"></i> Backup Data</a>
        </div>

        <div x-show="!sidebarOpen && hovered" x-transition:enter="transition ease-out duration-150" class="absolute left-24 top-0 bg-[#0b2428] border border-emerald-900/40 rounded-xl shadow-2xl z-50 w-56 overflow-hidden py-1.5 pointer-events-auto">
            <div class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-800/60 mb-1 select-none">System</div>


            @can('akses-developer')
                <a href="{{ route('setting.index') }}" class="flex items-center gap-3 px-5 py-2 text-sm text-slate-400 hover:bg-[#123A61] hover:text-white group">
                    <i class="ri-settings-4-line text-base opacity-60 group-hover:opacity-100"></i> Pengaturan Toko
                </a>
                <a href="{{ route('developer.modules.index') }}" class="flex items-center gap-3 px-5 py-2 text-sm text-slate-400 hover:bg-[#123A61] hover:text-white group">
                    <i class="ri-shield-keyhole-line text-base opacity-60 group-hover:opacity-100"></i> Akses Modul Client
                </a>
                <a href="{{ url('/developer') }}" class="flex items-center gap-3 px-5 py-2 text-sm text-slate-400 hover:bg-[#123A61] hover:text-white group">
                    <i class="ri-code-s-slash-line text-base opacity-60 group-hover:opacity-100"></i> Developer
                </a>
            @endcan

            <a href="{{ url('/backup-data') }}" class="flex items-center gap-3 px-4 py-2 text-sm text-slate-400 hover:bg-emerald-500/10 hover:text-emerald-400 transition"><i class="ri-database-line opacity-90"></i> Backup Data</a>
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