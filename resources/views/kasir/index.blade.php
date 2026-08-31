@extends('layouts.app')

@section('title', 'Kasir - Daftar Pesanan Jasa')

@section('content')





<x-page-header title="POS / Kasir" subtitle="Daftar pesanan yang siap diproses menjadi Nota">
    
</x-page-header>



<!-- Container Card Utama -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-5">
    
    <!-- Filter & Pencarian -->
    <div class="mb-5 flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="GET" action="{{ route('kasir.index') }}" class="w-full sm:w-80 relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <i class="ri-search-line"></i>
            </span>
            <input type="text" name="search" value="{{ $search ?? '' }}" 
                   placeholder="Cari No. WO / Pelanggan..." 
                   class="w-full text-sm border border-slate-300 rounded-lg pl-9 pr-3 py-2 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 outline-none">
        </form>
    </div>

    <!-- Tabel Data WO -->
    <div class="overflow-x-auto border border-slate-100 rounded-lg">
       <!-- Tabel Data WO -->
        <x-table>
            <x-table-header>
                <tr>
                    <x-table-head class="p-3 text-left font-bold">No WO</x-table-head>
                    <x-table-head class="p-3 text-left font-bold">Tanggal</x-table-head>
                    <x-table-head class="p-3 text-left font-bold">Operator</x-table-head>
                    <x-table-head class="p-3 text-left font-bold">Pelanggan</x-table-head>
                    <x-table-head class="p-3 text-center font-bold">Status</x-table-head>
                    <x-table-head class="p-3 text-right font-bold">Total</x-table-head>
                    <x-table-head class="p-3 text-center font-bold">Aksi</x-table-head>
                </tr>
            </x-table-header>

            <tbody>
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-3 font-semibold text-slate-800 font-mono text-sm">
                            {{ $order->no_pesanan }}
                        </td>

                        <td class="p-3 text-slate-500 ">
                            {{ $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '-' }}
                        </td>

                        <td class="p-3 text-slate-700 font-medium text-sm">
                            {{ $order->operator->name ?? 'Admin' }}
                        </td>

                        <td class="p-3 text-slate-700 text-sm">
                            @if($order->customer)
                                <span class="font-semibold text-indigo-900">{{ $order->customer->nama }}</span>
                            @else
                                <span class="text-slate-400 italic">{{ $order->customer_name_manual ?? 'Umum (Non-Member)' }}</span>
                            @endif
                        </td>

                        <td class="p-3 text-center">
                            <x-badge color="yellow">ORDER</x-badge>
                        </td>

                        <td class="p-3 text-right font-bold text-slate-900 font-mono">
                            Rp {{ number_format($order->orderItems?->sum('subtotal') ?? $order->items?->sum('subtotal') ?? 0, 0, ',', '.') }}
                        </td>

                        <td class="p-3 text-center">
                            <a href="{{ route('kasir.create', ['order_id' => $order->id]) }}" 
                               class="inline-flex items-center justify-center gap-1 bg-indigo-600 hover:bg-indigo-700 text-white font-medium  px-2.5 py-1 rounded-md transition shadow-sm">
                                <i class="ri-money-dollar-box-line"></i> Bayar / Process
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <x-empty-state 
                                icon="ri-shopping-cart-line" 
                                title="Belum ada pesanan" 
                                description="Tidak ada Work Order (WO) yang siap dibayar saat ini." 
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection