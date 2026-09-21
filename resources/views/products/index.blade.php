@extends('layouts.app')

@section('title', 'Master Produk')

@section('content')
<div x-data="{ openModal: false, modalTitle: '', modalStocks: [] }">
    <x-page-header title="Master Produk" subtitle="Kelola data Barang dan Jasa">
        <x-slot:action>
            @if(in_array(strtolower(auth()->user()->role), ['owner', 'admin']))
                <a href="{{ route('products.create') }}">
                    <x-button color="green">
                        <i class="ri-add-line"></i> Tambah Produk
                    </x-button>
                </a>
            @endif
        </x-slot:action>
    </x-page-header>

    <x-card>
        <form method="GET" class="mb-6">
            <div class="flex flex-wrap gap-3">
                <!-- Filter Search -->
                <div class="w-[280px]">
                    <x-input name="search" placeholder="Cari Nama / Barcode / Brand..." :value="request('search')" />
                </div>

                <!-- Filter Cabang -->
                <div class="w-48">
                    @if(in_array(strtolower(auth()->user()->role), ['owner', 'admin']))
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
                            name="branch_display"
                        />
                        <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                    @endif
                </div>

                <!-- Filter Tipe -->
                <div class="w-40">
                    <x-select name="type">
                        <option value="">Semua Tipe</option>
                        <option value="barang" @selected(request('type') == 'barang')>Barang</option>
                        <option value="jasa" @selected(request('type') == 'jasa')>Jasa</option>
                    </x-select>
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
                    @if($product->type === 'jasa')
                        <tr class="hover:bg-slate-50/50">
                            <td class="p-3 font-mono text-sm font-semibold text-slate-700">{{ $product->sku ?: '-' }}</td>
                            <td class="p-3 font-semibold text-slate-800">
                                {{ $product->name }}
                                <div class="text-xs font-normal text-slate-400">{{ $product->satuan }}</div>
                            </td>
                            <td class="p-3 text-xs text-slate-400 italic">Semua Cabang</td>
                            <td class="p-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">Jasa</span>
                            </td>
                            <td class="p-3 text-slate-600">-</td>
                            <td class="p-3 text-right font-mono font-medium text-slate-800">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center text-xs text-slate-400">-</td>
                            <td class="p-3 text-center">
                                <x-badge :color="$product->is_active ? 'green' : 'red'">
                                    {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </td>
                            <td class="p-3 text-center">
                                <a href="{{ route('products.edit', $product) }}">
                                    <x-button size="sm" color="green"><i class="ri-edit-line"></i></x-button>
                                </a>
                            </td>
                        </tr>
                    @else
                        @forelse($product->stocks as $pStock)
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
                                <td class="p-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Barang</span>
                                </td>
                                <td class="p-3 text-slate-600">{{ $product->supplier->name ?? '-' }}</td>
                                <td class="p-3 text-right font-mono font-medium text-slate-800">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="p-3 text-center">
                                    @php $isLow = $pStock->stock <= $pStock->min_stock; @endphp
                                    <div class="inline-flex items-center gap-1.5">
                                        <span class="font-mono px-2 py-0.5 rounded text-xs {{ $isLow ? 'bg-red-50 text-red-600 font-bold border border-red-200' : 'text-slate-800 font-semibold' }}">
                                            {{ number_format($pStock->stock) }}
                                        </span>
                                        
                                        <!-- Tombol Eye untuk Pop-Up Detail Stok Cabang -->
                                        <button 
                                            type="button" 
                                            class="text-slate-400 hover:text-emerald-600 transition"
                                            title="Lihat Stok Semua Cabang"
                                            @click="
                                                modalTitle = '{{ addslashes($product->name) }}';
                                                modalStocks = {{ json_encode($product->stocks->map(fn($s) => [
                                                    'branch' => $s->branch->name ?? '-',
                                                    'stock' => number_format($s->stock),
                                                    'unit' => $product->satuan
                                                ])) }};
                                                openModal = true;
                                            "
                                        >
                                            <i class="ri-eye-line text-base"></i>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-3 text-center">
                                    <x-badge :color="$product->is_active ? 'green' : 'red'">
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('products.edit', $product) }}">
                                        <x-button size="sm" color="green"><i class="ri-edit-line"></i></x-button>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-3 font-mono text-sm font-semibold text-slate-700">{{ $product->sku ?: '-' }}</td>
                                <td class="p-3 font-semibold text-slate-800">{{ $product->name }}</td>
                                <td class="p-3 text-xs text-slate-400">-</td>
                                <td class="p-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">Barang</span>
                                </td>
                                <td class="p-3 text-slate-600">{{ $product->supplier->name ?? '-' }}</td>
                                <td class="p-3 text-right font-mono font-medium text-slate-800">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </td>
                                <td class="p-3 text-center text-xs text-red-500 font-bold">0</td>
                                <td class="p-3 text-center">
                                    <x-badge :color="$product->is_active ? 'green' : 'red'">
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </x-badge>
                                </td>
                                <td class="p-3 text-center">
                                    <a href="{{ route('products.edit', $product) }}">
                                        <x-button size="sm" color="green"><i class="ri-edit-line"></i></x-button>
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    @endif
                @empty
                    <tr>
                        <td colspan="9">
                            <x-empty-state icon="ri-archive-line" title="Belum ada Produk" description="Data produk tidak ditemukan untuk filter ini." />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-table>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    </x-card>

    <!-- POP-UP / MODAL DETAIL STOK PER CABANG -->
    <div 
        x-show="openModal" 
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
        @keydown.escape.window="openModal = false"
    >
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6 border border-slate-100" @click.away="openModal = false">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-800">Detail Stok Cabang</h3>
                    <p class="text-xs text-slate-500 mt-0.5" x-text="'Produk: ' + modalTitle"></p>
                </div>
                <button @click="openModal = false" class="text-slate-400 hover:text-slate-600 text-lg">
                    <i class="ri-close-line"></i>
                </button>
            </div>

            <div class="mt-4">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 text-left text-xs uppercase tracking-wider">
                            <th class="p-2.5 rounded-l-md">Cabang</th>
                            <th class="p-2.5 text-right rounded-r-md">Stok Saat Ini</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="(item, index) in modalStocks" :key="index">
                            <tr class="hover:bg-slate-50/50">
                                <td class="p-2.5 text-slate-700 font-medium" x-text="item.branch"></td>
                                <td class="p-2.5 text-right font-mono font-semibold text-slate-800" x-text="item.stock + ' ' + item.unit"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="mt-6 text-right">
                <x-button @click="openModal = false" color="gray" size="sm">Tutup</x-button>
            </div>
        </div>
    </div>
</div>
@endsection