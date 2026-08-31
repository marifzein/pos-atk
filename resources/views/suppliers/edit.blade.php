@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<x-page-header title="Edit Supplier" subtitle="Perbarui data Supplier">
    <x-slot:action>
        <a href="{{ route('suppliers.index') }}">
            <x-button color="gray">
                <i class="ri-arrow-left-line"></i> Kembali
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

<x-card>
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-input label="Nama Supplier" name="name" icon="ri-store-2-line" required :value="old('name', $supplier->name)" />
            <x-input label="PIC (Person In Charge)" name="pic" icon="ri-user-settings-line" :value="old('pic', $supplier->pic)" />
            <x-input label="Telepon" name="phone" icon="ri-phone-line" :value="old('phone', $supplier->phone)" />
            <x-input label="Email" name="email" type="email" icon="ri-mail-line" :value="old('email', $supplier->email)" />
            
            <x-textarea label="Catatan" name="catatan" rows="3" placeholder="Catatan internal supplier...">{{ old('catatan', $supplier->catatan) }}</x-textarea>
            
            <div class="flex items-center mt-8">
                <!-- FIX BUG: menggunakan parsing stateless boolean langsung -->
                <x-checkbox 
                    label="Supplier Aktif" 
                    name="is_active" 
                    value="1" 
                    :checked="$supplier->is_active"
                />
            </div>
        </div>

        <div class="mt-6">
            <x-textarea label="Alamat" name="address" rows="4">{{ old('address', $supplier->address) }}</x-textarea>
        </div>

        <div class="flex justify-end gap-3 mt-8 border-t border-slate-100 pt-5">
            <a href="{{ route('suppliers.index') }}">
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