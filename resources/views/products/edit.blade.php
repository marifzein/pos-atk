@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<x-page-header title="Edit Produk" subtitle="Perbarui spesifikasi item produk">
    <x-slot:action>
        <a href="{{ route('products.index') }}">
            <x-button color="gray">
                <i class="ri-arrow-left-line"></i> Kembali
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

<x-card>
    <form 
        method="POST" 
        action="{{ route('products.update', $product) }}"
        x-data="{ 
            productType: @js(old('type', $product->type)),
            isCustom: @js((bool) old('is_custom_price', $product->is_custom_price))
        }"
    >    
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-input label="Nama Produk *" name="name" icon="ri-text" required :value="old('name', $product->name)" />
            <x-input label="Barcode" name="barcode" icon="ri-barcode-line" :value="old('barcode', $product->barcode)" />
            <x-input label="Kode Barang/SKU" name="sku" icon="ri-bookmark-line" :value="old('sku', $product->sku)" readonly />
            
            <x-select-custom label="Satuan Barang" name="satuan" :value="old('satuan', $product->satuan)" required>
                <x-select-option-custom value="pcs">pcs (Pieces)</x-select-option-custom>
                <x-select-option-custom value="buah">buah</x-select-option-custom>
                <x-select-option-custom value="lembar">lembar</x-select-option-custom>
                <x-select-option-custom value="rim">rim</x-select-option-custom>
                <x-select-option-custom value="buku">buku</x-select-option-custom>
                <x-select-option-custom value="pack">pack</x-select-option-custom>
                <x-select-option-custom value="box">box</x-select-option-custom>
                <x-select-option-custom value="dus">dus</x-select-option-custom>
                <x-select-option-custom value="lusin">lusin</x-select-option-custom>
                <x-select-option-custom value="roll">roll</x-select-option-custom>
                <x-select-option-custom value="set">set</x-select-option-custom>
                <x-select-option-custom value="hal">hal (Halaman)</x-select-option-custom>
                <x-select-option-custom value="kg">kg</x-select-option-custom>
                <x-select-option-custom value="liter">liter</x-select-option-custom>
                <x-select-option-custom value="meter">meter</x-select-option-custom>
            </x-select-custom>

            <!-- TIPE PRODUK -->
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700 select-none">
                    Tipe Produk <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-6 mt-2 h-10">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input 
                            type="radio" 
                            name="type" 
                            value="barang" 
                            x-model="productType"
                            @change="isCustom = false"
                            class="w-4 h-4 text-green-600 focus:ring-green-500 border-slate-300"
                        >
                        <span class="text-sm font-medium text-slate-700">Barang</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input 
                            type="radio" 
                            name="type" 
                            value="jasa" 
                            x-model="productType"
                            class="w-4 h-4 text-green-600 focus:ring-green-500 border-slate-300"
                        >
                        <span class="text-sm font-medium text-slate-700">Jasa</span>
                    </label>
                </div>
            </div>

            <!-- CHECKBOX JASA CUSTOM -->
            <div 
                class="flex items-center transition-all duration-200 mt-6"
                :class="productType !== 'jasa' ? 'opacity-40 pointer-events-none' : ''"
            >
                <x-checkbox 
                    id="is_custom_price"
                    label="Jasa Custom (Harga Diinput Saat Transaksi)" 
                    name="is_custom_price" 
                    value="1" 
                    x-bind:disabled="productType !== 'jasa'"
                    x-bind:checked="productType === 'jasa' && isCustom"
                    @change="isCustom = $event.target.checked"
                />
            </div>

            <x-select-custom label="Supplier / Vendor" name="supplier_id" :value="old('supplier_id', $product->supplier_id ?? '')">
                <x-select-option-custom value="">-- Pilih Supplier --</x-select-option-custom>
                @foreach($suppliers as $supplier)
                    <x-select-option-custom value="{{ $supplier->id }}">{{ $supplier->name }}</x-select-option-custom>
                @endforeach
            </x-select-custom>

            <x-input label="Harga Beli (Rp)" name="purchase_price" type="number" icon="ri-money-dollar-circle-line" required :value="old('purchase_price', $product->purchase_price)" />
            <x-input label="Harga Jual (Rp)" name="price" type="number" icon="ri-price-tag-3-line" required :value="old('price', $product->price)" />

            <div class="md:col-span-2">
                <x-textarea label="Catatan Keterangan" name="catatan" rows="2">{{ old('catatan', $product->catatan) }}</x-textarea>
            </div>
            
            <div class="flex items-center md:col-span-2">
                <x-checkbox label="Produk Aktif / Dijual" name="is_active" value="1" :checked="$product->is_active" />
            </div>
        </div>

        <!-- TABEL STOK PER CABANG (READONLY STOK & EDITABLE MIN_STOCK) -->
        <div x-show="productType === 'barang'" class="mt-8 pt-6 border-t border-slate-200">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-slate-800 flex items-center gap-2">
                    <i class="ri-store-2-line text-emerald-600"></i> Informasi Stok per Cabang
                </h3>
                <p class="text-xs text-slate-500">Stok fisik diubah melalui transaksi Penerimaan/Stok Opname. Anda dapat memperbarui batas Minimal Stok di sini.</p>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-lg">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 uppercase font-medium">
                        <tr>
                            <th class="px-4 py-3">Cabang</th>
                            <th class="px-4 py-3 w-48">Stok Saat Ini</th>
                            <th class="px-4 py-3 w-48">Minimal Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($branches as $branch)
                            @php
                                $stockItem = $productStocks->get($branch->id);
                                $currentStock = $stockItem ? $stockItem->stock : 0;
                                $minStock = $stockItem ? $stockItem->min_stock : 0;
                            @endphp
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    {{ $branch->name }}
                                </td>
                                <td class="px-4 py-3 font-mono font-bold text-slate-900">
                                    {{ number_format($currentStock) }} {{ $product->satuan }}
                                </td>
                                <td class="px-4 py-2">
                                    <input 
                                        type="number" 
                                        name="branches[{{ $branch->id }}][min_stock]" 
                                        value="{{ old('branches.'.$branch->id.'.min_stock', $minStock) }}" 
                                        min="0"
                                        class="w-full rounded-md border-slate-300 shadow-xs focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                                    />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-8 border-t border-slate-100 pt-5">
            <a href="{{ route('products.index') }}">
                <x-button color="secondary" type="button">
                    <i class="ri-close-circle-line text-red-500"></i> Batal
                </x-button>
            </a>
            <x-button color="green" type="submit">
                <i class="ri-save-line"></i> Simpan Perubahan
            </x-button>
        </div>
    </form>
</x-card>
@endsection