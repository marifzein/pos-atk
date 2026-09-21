@extends('layouts.app')

@section('title', 'Master Pelanggan')

@section('content')
<div class="px-3 py-4 space-y-4 pb-24">
    <!-- Header Mobile -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Pelanggan</h1>
            <p class="text-xs text-slate-500">Kelola data pelanggan toko</p>
        </div>
        <a href="{{ route('customers.create') }}">
            <x-button size="sm" color="green">
                <i class="ri-add-line"></i> Tambah
            </x-button>
        </a>
    </div>

    <!-- Form Carian -->
    <form method="GET" action="{{ route('customers.index') }}">
        <div class="flex gap-2">
            <div class="grow">
                <x-input name="search" placeholder="Cari nama, kode, telp..." :value="request('search')" icon="ri-search-line" />
            </div>
            <x-button type="submit" color="green" class="shrink-0">Cari</x-button>
            @if(request('search'))
                <a href="{{ route('customers.index') }}" class="shrink-0">
                    <x-button type="button" color="gray"><i class="ri-refresh-line"></i></x-button>
                </a>
            @endif
        </div>
    </form>

    <!-- Card List Pelanggan -->
    <div class="space-y-3">
        @forelse($customers as $customer)
            <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <span class="font-mono text-xs text-slate-400 block">{{ $customer->kode_pelanggan }}</span>
                        <h3 class="font-bold text-slate-800 text-base leading-tight">{{ $customer->nama }}</h3>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        @if($customer->is_member)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-green-50 text-green-700 border border-green-200">
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

                <div class="text-xs space-y-1 text-slate-600 border-t border-slate-100 pt-2.5">
                    <div class="flex items-center gap-2">
                        <i class="ri-phone-line text-slate-400"></i>
                        <span>{{ $customer->telepon ?: '-' }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <i class="ri-map-pin-line text-slate-400 mt-0.5"></i>
                        <span class="line-clamp-2">{{ $customer->alamat ?: '-' }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-2">
                    <a href="{{ route('customers.show', $customer) }}" class="grow">
                        <x-button size="sm" color="gray" class="w-full">
                            <i class="ri-eye-line"></i> Detail
                        </x-button>
                    </a>
                    <a href="{{ route('customers.edit', $customer) }}" class="grow">
                        <x-button size="sm" color="green" class="w-full">
                            <i class="ri-edit-line"></i> Edit
                        </x-button>
                    </a>
                </div>
            </div>
        @empty
            <x-card class="py-8">
                <x-empty-state icon="ri-user-3-line" title="Belum ada Pelanggan" description="Klik Tambah untuk mengisi data baru." />
            </x-card>
        @endforelse
    </div>

    <!-- Paginasi -->
    <div class="pt-2">
        {{ $customers->links() }}
    </div>
</div>
@endsection