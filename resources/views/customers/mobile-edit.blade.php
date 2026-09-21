@extends('layouts.app')

@section('title', 'Edit Pelanggan')

@section('content')
<div class="px-3 py-4 space-y-4 pb-24">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Edit Pelanggan</h1>
            <p class="text-xs text-slate-500">{{ $customer->kode_pelanggan }} - {{ $customer->nama }}</p>
        </div>
        <a href="{{ route('customers.index') }}">
            <x-button size="sm" color="gray">
                <i class="ri-arrow-left-line"></i> Kembali
            </x-button>
        </a>
    </div>

    <x-card class="p-4">
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <x-input label="Nama Pelanggan" name="nama" icon="ri-user-3-line" required :value="old('nama', $customer->nama)" />

            <x-input label="Telepon" name="telepon" icon="ri-phone-line" :value="old('telepon', $customer->telepon)" />

            <x-input label="Email" name="email" type="email" icon="ri-mail-line" :value="old('email', $customer->email)" />

            <div class="p-3 bg-slate-50 rounded-lg border border-slate-200">
                <x-checkbox 
                    label="Member" 
                    name="is_member" 
                    value="1"
                    :checked="old('is_member', $customer->is_member)" 
                />
            </div>

            <!-- STATUS (INPUT RADIO) -->
            <div class="space-y-1">
                <label class="text-sm font-medium text-slate-700 select-none">
                    Status <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3 mt-1">
                    <label class="flex items-center justify-center gap-2 p-2.5 rounded-lg border cursor-pointer border-slate-200 has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                        <input 
                            type="radio" 
                            name="status" 
                            value="1" 
                            {{ old('status', (string) $customer->status) === '1' ? 'checked' : '' }}
                            class="w-4 h-4 text-green-600 focus:ring-green-500 border-slate-300"
                        >
                        <span class="text-sm font-medium text-slate-700">Aktif</span>
                    </label>

                    <label class="flex items-center justify-center gap-2 p-2.5 rounded-lg border cursor-pointer border-slate-200 has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                        <input 
                            type="radio" 
                            name="status" 
                            value="0" 
                            {{ old('status', (string) $customer->status) === '0' ? 'checked' : '' }}
                            class="w-4 h-4 text-red-600 focus:ring-red-500 border-slate-300"
                        >
                        <span class="text-sm font-medium text-slate-700">Nonaktif</span>
                    </label>
                </div>
            </div>

            <x-textarea label="Alamat" name="alamat" rows="3">{{ old('alamat', $customer->alamat) }}</x-textarea>

            <x-textarea label="Catatan" name="catatan" rows="2">{{ old('catatan', $customer->catatan) }}</x-textarea>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('customers.index') }}" class="grow">
                    <x-button color="secondary" type="button" class="w-full">
                        Batal
                    </x-button>
                </a>
                <x-button color="primary" type="submit" class="grow">
                    <i class="ri-save-line"></i> Simpan
                </x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection