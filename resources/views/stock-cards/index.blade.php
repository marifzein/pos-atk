@extends('layouts.app')

@section('title', 'Kartu Stok Produk')

@section('content')
<div>
    <x-page-header title="Kartu Stok Produk" subtitle="Pantau pergerakan dan sisa stok barang di seluruh cabang">
    </x-page-header>

    <x-card>
        <form method="GET" class="mb-6">
            <div class="flex flex-wrap gap-3">
                <!-- Filter Search -->
                <div class="w-[280px]">
                    <x-input name="search" placeholder="Cari Nama / Barcode / Brand..." :value="request('search')" />
                </div>

                <!-- Filter Cabang (Hanya Owner & Admin yang bisa memilih Semua Cabang) -->
                <div class="w-48">
                    @if($isGlobalUser)
                        <x-select name="branch_id">
                            <option value="">Semua Cabang</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" @selected($selectedBranchId == $branch->id)>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </x-select>
                    @else
                        <x-input 
                            value="{{ auth()->user()->branch->name ?? 'Cabang Anda' }}" 
                            readonly 
                            class="bg-slate-100 font-medium text-slate-600"
                        />
                        <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                    @endif
                </div>

                <!-- Filter Stok -->
                <div class="w-40">
                    <x-select name="stock">
                        <option value="">Semua Stok</option>
                        <option value="available" @selected(request('stock') == 'available')>Tersedia</option>
                        <option value="low" @selected(request('stock') == 'low')>Menipis</option>
                        <option value="empty" @selected(request('stock') == 'empty')>Habis</option>
                    </x-select>
                </div>

                <x-button type="submit" color="green"><i class="ri-filter-3-line"></i> Cari</x-button>
            </div>
        </form>

        <x-table>
            <x-table-header>
                <tr class="text-left">
                    <x-table-head>Kode/SKU</x-table-head>
                    <x-table-head>Nama Produk</x-table-head>
                    <x-table-head>Cabang</x-table-head>
                    <x-table-head>Supplier</x-table-head>
                    <x-table-head class="text-right">Harga Jual</x-table-head>
                    <x-table-head class="text-center">Stok</x-table-head>
                    <x-table-head class="text-center">Status</x-table-head>
                    <x-table-head class="text-center">Aksi</x-table-head>
                </tr>
            </x-table-header>
            <tbody>
                @forelse($products as $product)
                    @forelse($product->stocks as $pStock)
                        @php 
                            $isLow = $pStock->stock > 0 && $pStock->stock <= $pStock->min_stock; 
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3 font-mono text-sm font-semibold text-slate-700">{{ $product->sku ?: '-' }}</td>
                            <td class="p-3 font-semibold text-slate-800">
                                {{ $product->name }}
                                <div class="text-xs font-normal text-slate-400">{{ $product->satuan }}</div>
                            </td>
                            <td class="p-3">
                                <span class="font-medium text-slate-700 text-xs bg-slate-100 px-2 py-1 rounded border border-slate-200">
                                    <i class="ri-store-2-line text-emerald-600"></i> {{ $pStock->branch->name ?? '-' }}
                                </span>
                            </td>
                            <td class="p-3 text-slate-600">{{ $product->supplier->name ?? '-' }}</td>
                            <td class="p-3 text-right font-mono font-medium text-slate-800">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="font-mono px-2 py-0.5 rounded text-xs {{ $pStock->stock <= 0 ? 'bg-red-50 text-red-600 font-bold border border-red-200' : ($isLow ? 'bg-amber-50 text-amber-600 font-bold border border-amber-200' : 'text-slate-800 font-semibold') }}">
                                    {{ number_format($pStock->stock) }}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                @if($pStock->stock <= 0)
                                    <x-badge color="red">Habis</x-badge>
                                @elseif($isLow)
                                    <x-badge color="yellow">Menipis</x-badge>
                                @else
                                    <x-badge color="green">Tersedia</x-badge>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                <!-- Pass branch_id via URL parameter -->
                                <a href="{{ route('stock-cards.show', [$product->id, 'branch_id' => $pStock->branch_id]) }}" title="Lihat Mutasi Stok">
                                    <x-button size="sm" color="blue">
                                        <i class="ri-file-chart-line"></i>
                                    </x-button>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3 font-mono text-sm font-semibold text-slate-700">{{ $product->sku ?: '-' }}</td>
                            <td class="p-3 font-semibold text-slate-800">
                                {{ $product->name }}
                                <div class="text-xs font-normal text-slate-400">{{ $product->satuan }}</div>
                            </td>
                            <td class="p-3 text-xs text-slate-400">-</td>
                            <td class="p-3 text-slate-600">{{ $product->supplier->name ?? '-' }}</td>
                            <td class="p-3 text-right font-mono font-medium text-slate-800">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center text-xs text-red-500 font-bold">0</td>
                            <td class="p-3 text-center">
                                <x-badge color="red">Habis</x-badge>
                            </td>
                            <td class="p-3 text-center">
                                <a href="{{ route('stock-cards.show', $product->id) }}" title="Lihat Mutasi Stok">
                                    <x-button size="sm" color="blue">
                                        <i class="ri-file-chart-line"></i>
                                    </x-button>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                @empty
                    <tr>
                        <td colspan="8">
                            <x-empty-state icon="ri-archive-line" title="Belum ada Produk Barang" description="Data produk tidak ditemukan untuk filter ini." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    </x-card>
</div>
@endsection