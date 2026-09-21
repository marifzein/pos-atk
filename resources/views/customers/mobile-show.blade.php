@extends('layouts.app')

@section('title', 'Detail Pelanggan')

@section('content')
<div class="px-3 py-4 space-y-4 pb-24">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Detail Pelanggan</h1>
            <p class="text-xs text-slate-500">{{ $customer->kode_pelanggan }}</p>
        </div>
        <a href="{{ route('customers.index') }}">
            <x-button size="sm" color="gray">
                <i class="ri-arrow-left-line"></i> Kembali
            </x-button>
        </a>
    </div>

    <x-card class="p-4 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h2 class="text-lg font-bold text-slate-800">{{ $customer->nama }}</h2>
                <span class="text-xs text-slate-500 font-mono">{{ $customer->kode_pelanggan }}</span>
            </div>
            <div class="flex items-center gap-2">
                @if($customer->is_member)
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                        Member
                    </span>
                @endif

                @if($customer->status)
                    <x-badge color="green">Aktif</x-badge>
                @else
                    <x-badge color="red">Nonaktif</x-badge>
                @endif
            </div>
        </div>

        <div class="space-y-3 text-sm text-slate-700">
            <div>
                <span class="text-xs text-slate-400 block font-medium">Telepon</span>
                <span class="font-semibold">{{ $customer->telepon ?: '-' }}</span>
            </div>

            <div>
                <span class="text-xs text-slate-400 block font-medium">Email</span>
                <span>{{ $customer->email ?: '-' }}</span>
            </div>

            <div>
                <span class="text-xs text-slate-400 block font-medium">Alamat</span>
                <p class="text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100 mt-1">{{ $customer->alamat ?: '-' }}</p>
            </div>

            <div>
                <span class="text-xs text-slate-400 block font-medium">Catatan</span>
                <p class="text-slate-600 bg-slate-50 p-2.5 rounded-lg border border-slate-100 mt-1">{{ $customer->catatan ?: '-' }}</p>
            </div>
        </div>

        <div class="pt-3 border-t border-slate-100 flex gap-2">
            <a href="{{ route('customers.edit', $customer) }}" class="w-full">
                <x-button color="green" class="w-full">
                    <i class="ri-edit-line"></i> Edit Pelanggan
                </x-button>
            </a>
        </div>
    </x-card>
</div>
@endsection