@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h2 class="text-2xl font-bold text-slate-800">Dashboard</h2>
    <p class="text-slate-500 mt-1">Ringkasan aktivitas ritel hari ini</p>
</div>

<!-- Grid Ringkasan Statis -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100 flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Penjualan</span>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">Rp 0</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl">
            <i class="ri-money-dollar-circle-line"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl shadow-sm p-5 border border-slate-100 flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Transaksi Berhasil</span>
            <h3 class="text-2xl font-bold text-slate-800 mt-1">0</h3>
        </div>
        <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl">
            <i class="ri-shopping-cart-2-line"></i>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm p-6 mt-8 border border-slate-100">
    <h3 class="font-bold text-slate-800 mb-4">Grafik Transaksi Mingguan</h3>
    <div class="h-48 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 text-sm">
        [ Area Chart Canvas ]
    </div>
</div>
@endsection