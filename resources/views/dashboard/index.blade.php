@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h2 class="text-2xl font-bold text-slate-800">Dashboard</h2>
    <p class="text-slate-500 mt-1">Ringkasan aktivitas hari ini</p>
</div>

<div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Dashboard</h2>
        <p class="text-slate-500 mt-1">
            Periode: <span class="font-semibold text-slate-700">{{ $startDate->format('d M Y') }}</span> 
            s/d <span class="font-semibold text-slate-700">{{ $endDate->format('d M Y') }}</span>
        </p>
    </div>

    <!-- Tombol Filter Tanggal Cepat -->
    <div class="flex items-center gap-2 bg-slate-100 p-1.5 rounded-2xl">
        <a href="{{ route('dashboard', ['period' => 'today']) }}" 
           class="px-4 py-2 text-xs font-semibold rounded-xl transition {{ $period === 'today' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
           Hari Ini
        </a>
        <a href="{{ route('dashboard', ['period' => 'this_week']) }}" 
           class="px-4 py-2 text-xs font-semibold rounded-xl transition {{ $period === 'this_week' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
           Minggu Ini
        </a>
        <a href="{{ route('dashboard', ['period' => 'this_month']) }}" 
           class="px-4 py-2 text-xs font-semibold rounded-xl transition {{ $period === 'this_month' ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
           Bulan Ini
        </a>
    </div>
</div>

    <!-- Grid KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">

        <!-- KPI 1: Arus Kas Masuk Hari Ini -->
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Cash Inflow</span>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                <i class="ri-wallet-3-line"></i>
            </div>
        </div>
        <div>
            <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($totalCashInflow, 0, ',', '.') }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $totalCountInflow }} Transaksi Lunas</p>
        </div>
    </div>

    <!-- KPI 2: Omset Barang -->
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Omset Barang</span>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                <i class="ri-box-3-line"></i>
            </div>
        </div>
        <div>
            <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($omsetBarang, 0, ',', '.') }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $countTrxBarang }} Transaksi Barang</p>
        </div>
    </div>

    <!-- KPI 3: Omset Jasa -->
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Omset Jasa</span>
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl">
                <i class="ri-service-line"></i>
            </div>
        </div>
        <div>
            <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($omsetJasa, 0, ',', '.') }}</h3>
            <p class="text-xs text-slate-400 mt-1">{{ $countTrxJasa }} Transaksi Jasa</p>
        </div>
    </div>

    <!-- KPI 4: Laba Kotor -->
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100 flex flex-col justify-between">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Laba Kotor</span>
            <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl">
                <i class="ri-funds-line"></i>
            </div>
        </div>
        <div>
            <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($labaKotor, 0, ',', '.') }}</h3>
            <p class="text-xs text-slate-400 mt-1">Omset dikurangi HPP</p>
        </div>
    </div>

    <!-- KPI 5: Item Perlu Kulakan (Klik -> products.index?stock=low) -->
    <a href="{{ route('products.index', ['stock' => 'low']) }}" class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100 flex flex-col justify-between transition hover:border-amber-300 hover:shadow-md group">
        <div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 group-hover:text-amber-600">Stok Habis</span>
                {{-- <i class="ri-arrow-right-line text-slate-300 group-hover:text-amber-500"></i> --}}
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl">
                    
                    <i class="ri-error-warning-line"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2 mt-1">
                <span class="text-2xl font-bold text-slate-800">{{ $totalPerluKulakan }}</span>
                <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2 py-0,5 rounded-full">Barang</span>
            </div>
        </div>
        <div class="mt-3 pt-3 border-t border-slate-50 flex items-center gap-1,5 text-xs text-amber-600 font-medium">
            {{-- <i class="ri-error-warning-line mt-0.5 pr-4 text-xl"></i> --}}
            <div class="flex flex-col gap-0.5">
                <div>{{ $emptyStockCount }} Habis Total</div>
                <div>{{ $lowStockCount }} Stok Menipis</div>
            </div>
        </div>
    </a>

</div>

{{-- <div class="bg-white rounded-2xl shadow-sm p-6 mt-8 border border-slate-100">
    <h3 class="font-bold text-slate-800 mb-4">Grafik Transaksi Mingguan</h3>
    <div class="h-48 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 text-sm">
        [ Area Chart Canvas ]
    </div>
</div> --}}

<!-- Grid KPI Cards (Gunakan variabel $totalCashInflow, $omsetBarang, dll) -->
{{-- ... bagian Grid KPI Cards kamu ... --}}

<!-- GRID SECTION CHART -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">

    <!-- CHART 1: Tren Penjualan (Line Chart - 2 Kolom) -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
        <h3 class="font-bold text-slate-800 mb-4">Grafik Tren Omset Penjualan</h3>
        <div class="relative h-64">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </div>

    <!-- CHART 3: Metode Pembayaran (Donut/Pie Chart - 1 Kolom) -->
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
        <h3 class="font-bold text-slate-800 mb-4">Metode Pembayaran</h3>
        <div class="relative h-64 flex items-center justify-center">
            <canvas id="paymentMethodChart"></canvas>
        </div>
    </div>

    <!-- CHART 2: Top 5 Produk Terlaris (Bar Chart - Full Width) -->
    <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm p-6 border border-slate-100">
        <h3 class="font-bold text-slate-800 mb-4">Top 5 Produk Terlaris (Qty)</h3>
        <div class="relative h-64">
            <canvas id="topProductsChart"></canvas>
        </div>
    </div>

</div>

<!-- CDN Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // --- 1. TREN PENJUALAN (LINE CHART) ---
        const salesTrendData = @json($salesTrend);
        new Chart(document.getElementById('salesTrendChart'), {
            type: 'line',
            data: {
                labels: Object.keys(salesTrendData),
                datasets: [{
                    label: 'Omset (Rp)',
                    data: Object.values(salesTrendData),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // --- 2. METODE PEMBAYARAN (DONUT CHART) ---
        const paymentData = @json($paymentData);
        new Chart(document.getElementById('paymentMethodChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(paymentData),
                datasets: [{
                    data: Object.values(paymentData),
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // --- 3. TOP 5 PRODUK (BAR CHART) ---
        const topProductsData = @json($topProducts);
        new Chart(document.getElementById('topProductsChart'), {
            type: 'bar',
            data: {
                labels: topProductsData.map(item => item.nama_barang),
                datasets: [{
                    label: 'Terjual (Qty)',
                    data: topProductsData.map(item => item.total_qty),
                    backgroundColor: '#6366f1',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    });
</script>

@endsection