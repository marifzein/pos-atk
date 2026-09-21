@extends('layouts.mobile-app')

@section('title', 'Detail Pesanan Jasa')
@section('page_subtitle', 'WO: ' . $order->no_pesanan)

@section('content')
<div class="space-y-3 pb-20">

    <!-- TOMBOL KEMBALI DI ATAS -->
    <div>
        <a href="{{ route('pesanan-jasa.history') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 bg-white px-3 py-2 rounded-xl border border-slate-200 active:scale-95 transition">
            <i class="ri-arrow-left-line text-sm"></i>
            <span>Kembali ke Riwayat</span>
        </a>
    </div>

    <!-- 1. HEADER INFO WORK ORDER -->
    <div class="bg-white rounded-2xl p-4 shadow-xs border border-slate-100 space-y-3">
        <div class="flex justify-between items-start border-b border-slate-100 pb-2.5">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nomor Work Order</span>
                <span class="text-base font-black text-slate-900 font-mono tracking-tight">{{ $order->no_pesanan }}</span>
            </div>
            <div>
                @if(strtolower($order->status) === 'batal')
                    <span class="bg-rose-50 text-rose-600 border border-rose-200 text-xs font-black px-2.5 py-1 rounded-lg">BATAL</span>
                @elseif(strtolower($order->status) === 'lunas')
                    <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 text-xs font-black px-2.5 py-1 rounded-lg">LUNAS</span>
                @else
                    <span class="bg-amber-50 text-amber-600 border border-amber-200 text-xs font-black px-2.5 py-1 rounded-lg">{{ strtoupper($order->status) }}</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
                <span class="text-slate-400 block font-medium">Tanggal Transaksi</span>
                <span class="font-bold text-slate-700">{{ $order->created_at ? $order->created_at->format('d/m/Y H:i') : '-' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Operator / Kasir</span>
                <span class="font-bold text-slate-700">{{ $order->operator?->name ?? 'Admin' }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Pelanggan</span>
                <span class="font-bold text-indigo-900">{{ $order->customer?->nama ?? ($order->customer_name_manual ?? 'Umum (Non-Member)') }}</span>
            </div>
            <div>
                <span class="text-slate-400 block font-medium">Cabang</span>
                <span class="font-bold text-slate-700">{{ $order->branch->name ?? 'Pusat' }}</span>
            </div>
        </div>
    </div>

    <!-- ALERT JIKA DIBATALKAN -->
    @if(strtolower($order->status) === 'batal')
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-3.5 text-xs text-rose-800 space-y-1">
            <div class="font-bold text-rose-900 flex items-center gap-1 text-xs">
                <i class="ri-error-warning-fill text-rose-600"></i> Dibatalkan
            </div>
            <div>
                <span class="text-slate-500">Oleh:</span> 
                <span class="font-bold">{{ $order->pembatalan->user?->name ?? ($order->operator?->name ?? 'Admin') }}</span>
            </div>
            <div>
                <span class="text-slate-500">Alasan:</span> 
                <span class="italic">"{{ $order->pembatalan->alasan ?? ($order->catatan ?? 'Tidak ada alasan') }}"</span>
            </div>
        </div>
    @endif

    <!-- 2. DETAIL ITEM JASA -->
    <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-100 space-y-3">
        <h3 class="font-black text-slate-800 text-xs uppercase tracking-wider flex items-center gap-1.5 border-b border-slate-100 pb-2">
            <i class="ri-customer-service-2-line text-indigo-600"></i> Rincian Layanan Jasa
        </h3>

        <div class="space-y-2 divide-y divide-slate-100">
            @foreach($order->items as $item)
                <div class="pt-2 first:pt-0 space-y-1">
                    <div class="flex justify-between items-start">
                        <div class="font-bold text-slate-800 text-xs">
                            {{ $item->item_name }}
                        </div>
                        <div class="font-black text-slate-900 text-xs font-mono">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center text-[11px] text-slate-400">
                        <span>{{ $item->qty }} x @Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                        @if($item->notes)
                            <span class="italic text-slate-500 font-medium">({{ $item->notes }})</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 3. TOTAL BIAYA -->
    <div class="bg-slate-900 text-white rounded-2xl p-4 shadow-lg space-y-2">
        <div class="flex justify-between items-center text-xs text-slate-400">
            <span>Subtotal Layanan</span>
            <span class="font-bold text-slate-200">Rp {{ number_format($order->items->sum('subtotal'), 0, ',', '.') }}</span>
        </div>
        <div class="flex justify-between items-center pt-2 border-t border-slate-800">
            <span class="font-black text-sm uppercase tracking-wide">Total Tagihan</span>
            <span class="font-black text-xl text-emerald-400 font-mono">Rp {{ number_format($order->items->sum('subtotal'), 0, ',', '.') }}</span>
        </div>
    </div>

</div>
@endsection