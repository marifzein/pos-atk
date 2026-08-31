@extends('layouts.app')

@section('title', 'Detail Pesanan Barang')

@section('content')
<div class="max-w-5xl mx-auto p-4 sm:p-6">

    <div class="flex justify-end mb-4">
        <a href="{{ route('pesanan-barang.history') }}">
            <x-button color="secondary" class="px-5">
                <i class="ri-arrow-left-circle-line mr-1"></i> Kembali
            </x-button>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6 sm:p-8 border border-slate-100">
        <h1 class="text-xl font-bold text-slate-800 mb-6">Detail Pesanan Barang</h1>

        <div class="bg-slate-100/70 p-5 rounded-2xl border border-slate-200/80 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-3 gap-x-8 text-sm">
                <div class="space-y-2">
                    <div class="flex items-center">
                        <span class="w-32 text-slate-500 font-medium shrink-0">No SO:</span>
                        <span class="font-mono text-slate-800 font-bold text-lg">{{ $order->no_pesanan }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-32 text-slate-500 font-medium shrink-0">Tanggal:</span>
                        <span class="text-slate-800">{{ $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '-' }}</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-32 text-slate-500 font-medium shrink-0">Kasir / Operator:</span>
                        <span class="font-semibold text-slate-800">{{ $order->operator?->name ?? 'Admin' }}</span>
                    </div>
                </div>

                <div class="space-y-2 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200">
                    <div class="flex items-center">
                        <span class="w-24 text-slate-500 font-medium shrink-0">Pelanggan:</span>
                        <span class="font-semibold text-slate-800">
                            {{ $order->customer?->nama ?? ($order->customer_name_manual ?? 'Umum (Non-Member)') }}
                        </span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-24 text-slate-500 font-medium shrink-0">Status:</span>
                        @if(strtolower($order->status) === 'batal')
                            <span class="bg-red-100 text-red-700 px-2.5 py-0.5 rounded text-xs font-semibold">Batal</span>
                        @else
                            <span class="bg-green-100 text-green-700 px-2.5 py-0.5 rounded text-xs font-semibold">ORDER</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200/80 mb-6">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-100/70 text-slate-700 font-bold border-b border-slate-200/80">
                    <tr>
                        <th class="py-3.5 px-5">Nama Barang</th>
                        <th class="py-3.5 px-5 text-center w-24">Qty</th>
                        <th class="py-3.5 px-5 text-right w-40">Harga</th>
                        <th class="py-3.5 px-5 text-right w-44">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach($order->items as $item)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="py-3.5 px-5 font-medium text-slate-800">{{ $item->item_name }}</td>
                        <td class="py-3.5 px-5 text-center text-slate-700">{{ $item->qty }}</td>
                        <td class="py-3.5 px-5 text-right text-slate-700 whitespace-nowrap">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="py-3.5 px-5 text-right font-semibold text-slate-800 whitespace-nowrap">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex justify-end pt-2">
            <div class="w-full sm:w-80 space-y-3 text-sm">
                <div class="flex justify-between items-center text-slate-600 px-3">
                    <span>Subtotal</span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($order->items->sum('subtotal'), 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center font-extrabold text-base text-slate-900 bg-slate-100/90 py-3.5 px-4 rounded-xl border border-slate-200/80 mt-3">
                    <span>Total Tagihan</span>
                    <span class="text-lg">Rp {{ number_format($order->items->sum('subtotal'), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection