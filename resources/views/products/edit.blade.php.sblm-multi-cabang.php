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
    {{-- <form method="POST" action="{{ route('products.update', $product) }}"> --}}
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
            <x-input label="Barcode / SKU" name="barcode" icon="ri-barcode-line" :value="old('barcode', $product->barcode)" readonly />
            <x-input label="Brand / Merk" name="brand" icon="ri-bookmark-line" :value="old('brand', $product->brand)" />
            {{-- <x-input label="Satuan *" name="satuan" required :value="old('satuan', $product->satuan)" /> --}}
            
            <x-select-custom
                label="Satuan Barang"
                name="satuan"
                :value="old('satuan', 'pcs')"
                required
            >
                <x-select-option-custom value="pcs" {{ old('satuan', 'pcs') == 'pcs' ? 'selected' : '' }}>pcs (Pieces)</option>
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

            {{-- <x-select-custom label="Tipe Produk *" name="type" :value="old('type', $product->type)">
                <x-select-option-custom value="barang">Barang</x-select-option-custom>
                <x-select-option-custom value="jasa">Jasa</x-select-option-custom>
            </x-select-custom> --}}

            <!-- TIPE PRODUK (RADIO BUTTON) -->
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

            <!-- CHECKBOX JASA CUSTOM (STRICT REACTION VIA ALPINE) -->
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
            <x-input label="Stok" readonly name="stock" type="number" icon="ri-stack-line" required :value="old('stock', $product->stock)" />
            <x-input label="Minimal Stok" name="min_stock" type="number" icon="ri-alert-line" required :value="old('min_stock', $product->min_stock)" />
            
            <x-textarea label="Catatan Keterangan" name="catatan" rows="3">{{ old('catatan', $product->catatan) }}</x-textarea>
            
            <div class="flex items-center mt-8">
                <!-- Aman Bos menggunakan data stateless langsung -->
                <x-checkbox 
                    label="Produk Aktif / Dijual" 
                    name="is_active" 
                    value="1" 
                    :checked="$product->is_active"
                />
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