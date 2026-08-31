@extends('layouts.app')

@section('title', 'Master Produk')

@section('content')
<x-page-header title="Master Produk" subtitle="Kelola data Barang dan Jasa">
    <x-slot:action>
        <a href="{{ route('products.create') }}">
            <x-button color="green">
                <i class="ri-add-line"></i> Tambah Produk
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

<x-card>
    <form method="GET" class="mb-6">
        <div class="flex gap-3 max-w-md">
            <x-input name="search" placeholder="Cari Nama / Barcode / Brand..." :value="request('search')" />
            <x-button type="submit" color="green">Cari</x-button>
        </div>
    </form>

    <x-table>
        <x-table-header>
            <tr class="text-left">
                <x-table-head>Kode/SKU</x-table-head>
                <x-table-head>Barcode</x-table-head>
                <x-table-head>Nama Produk</x-table-head>
                <x-table-head>Brand</x-table-head>
                <x-table-head>Tipe</x-table-head>
                <x-table-head>Supplier</x-table-head>
                <x-table-head class="text-right">Harga Jual</x-table-head>
                <x-table-head class="text-center">Stok</x-table-head>
                <x-table-head class="text-center">Status</x-table-head>
                <x-table-head class="text-center">Aksi</x-table-head>
            </tr>
        </x-table-header>
        <tbody>
            @forelse($products as $product)
                <tr class="hover:bg-slate-50/50">
                    <td class="p-3 font-mono text-sm font-semibold text-slate-700">{{ $product->sku ?: '-' }}</td>
                    <td class="p-3 font-mono text-sm text-slate-500">{{ $product->barcode ?: '-' }}</td>
                    <td class="p-3 font-semibold text-slate-800">
                        {{ $product->name }}
                        <div class="text-xs font-normal text-slate-400">{{ $product->satuan }}</div>
                    </td>
                    <td class="p-3 text-slate-600">{{ $product->brand ?: '-' }}</td>
                    <td class="p-3">
                        @if($product->type === 'barang')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Barang</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">Jasa</span>
                        @endif
                    </td>
                    <td class="p-3 text-slate-600">{{ $product->supplier ? $product->supplier->name : '-' }}</td>
                    <td class="p-3 text-right font-mono font-medium text-slate-800">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                    <td class="p-3 text-center">
                        <span class="font-mono {{ $product->stock <= $product->min_stock ? 'text-red-600 font-bold' : 'text-slate-700' }}">
                            {{ $product->stock }}
                        </span>
                    </td>
                    <td class="p-3 text-center">
                        @if($product->is_active)
                            <x-badge color="green">Aktif</x-badge>
                        @else
                            <x-badge color="red">Nonaktif</x-badge>
                        @endif
                    </td>
                    <td class="p-3">
                        <div class="flex justify-center">
                            <a href="{{ route('products.edit', $product) }}">
                                <x-button size="sm" color="green">
                                    <i class="ri-edit-line"></i>
                                </x-button>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <x-empty-state icon="ri-archive-line" title="Belum ada Produk" description="Klik Tambah Produk untuk mengisi katalog data barang/jasa." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $products->links() }}
    </div>
</x-card>
@endsection