@extends('layouts.app')

@section('title', 'Edit Cabang')

@section('content')
<x-page-header title="Edit Cabang" subtitle="Perbarui data Outlet / Cabang">
    <x-slot:action>
        <a href="{{ route('branches.index') }}">
            <x-button color="gray">
                <i class="ri-arrow-left-line"></i> Kembali
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

<x-card>
    <form method="POST" action="{{ route('branches.update', $branch) }}">
        @csrf
        @method('PUT')
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-input label="Kode Cabang *" name="code" icon="ri-qr-code-line" required :value="old('code', $branch->code)" />
            <x-input label="Nama Cabang *" name="name" icon="ri-store-3-line" required :value="old('name', $branch->name)" />
            <x-input label="Telepon" name="phone" icon="ri-phone-line" :value="old('phone', $branch->phone)" />
            
            <div class="flex items-center mt-8">
                <x-checkbox 
                    label="Cabang Aktif" 
                    name="is_active" 
                    value="1" 
                    :checked="$branch->is_active"
                />
            </div>
        </div>

        <div class="mt-6">
            <x-textarea label="Alamat" name="address" rows="4">{{ old('address', $branch->address) }}</x-textarea>
        </div>

        <div class="flex justify-end gap-3 mt-8 border-t border-slate-100 pt-5">
            <a href="{{ route('branches.index') }}">
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