@extends('layouts.app')

@section('title', 'Tambah Pelanggan')

@section('content')
<x-page-header title="Tambah Pelanggan" subtitle="Tambahkan Pelanggan Baru">
    <x-slot:action>
        <a href="{{ route('customers.index') }}">
            <x-button color="gray">
                <i class="ri-arrow-left-line"></i> Kembali
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

<x-card>
    <form method="POST" action="{{ route('customers.store') }}">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-input label="Nama Pelanggan" name="nama" icon="ri-user-3-line" required :value="old('nama')" />
            <x-input label="Telepon" name="telepon" icon="ri-phone-line" :value="old('telepon')" />
            <x-input label="Email" name="email" type="email" icon="ri-mail-line" :value="old('email')" />
            
            <div class="flex items-center mt-8">
                

                <x-checkbox 
                    label="Member" 
                    name="is_member" 
                    :checked="old('is_member', $customer->is_member ?? true)" 
                />

            </div>

            

            <x-textarea label="Catatan" name="catatan" rows="3" placeholder="Catatan internal pelanggan...">{{ old('catatan') }}</x-textarea>
            
            {{-- <x-select-custom label="Status" name="status" :value="old('status', $customer->status ?? '1')">
                <x-select-option-custom value="1">Aktif</x-select-option-custom>
                <x-select-option-custom value="0">Nonaktif</x-select-option-custom>
            </x-select-custom> --}}
            
            
            <!-- STATUS (INPUT RADIO) -->
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700 select-none">
                    Status <span class="text-red-500">*</span>
                </label>
                <div class="flex items-center gap-6 mt-2 h-10">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input 
                            type="radio" 
                            name="status" 
                            value="1" 
                            {{ old('status', '1') == '1' ? 'checked' : '' }}
                            class="w-4 h-4 text-green-600 focus:ring-green-500 border-slate-300"
                        >
                        <span class="text-sm font-medium text-slate-700">Aktif</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input 
                            type="radio" 
                            name="status" 
                            value="0" 
                            {{ old('status') == '0' ? 'checked' : '' }}
                            class="w-4 h-4 text-red-600 focus:ring-red-500 border-slate-300"
                        >
                        <span class="text-sm font-medium text-slate-700">Nonaktif</span>
                    </label>
                </div>
            </div>

        </div>

        <div class="mt-6">
            <x-textarea label="Alamat" name="alamat" rows="4" placeholder="Alamat lengkap pelanggan...">{{ old('alamat') }}</x-textarea>
        </div>

        <div class="flex justify-end gap-3 mt-8 border-t border-slate-100 pt-5">
            <a href="{{ route('customers.index') }}">
                <x-button color="secondary" type="button">
                    <i class="ri-close-circle-line text-red-500"></i> Batal
                </x-button>
            </a>
            <x-button color="primary" type="submit">
                <i class="ri-save-line"></i> Simpan Pelanggan
            </x-button>
        </div>
    </form>
</x-card>
@endsection