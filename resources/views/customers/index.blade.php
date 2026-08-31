@extends('layouts.app')

@section('title', 'Master Pelanggan')

@section('content')
<x-page-header title="Master Pelanggan" subtitle="Kelola data Pelanggan">
    <x-slot:action>
        <a href="{{ route('customers.create') }}">
            <!-- Diubah ke green/green sesuai tema -->
            <x-button color="green">
                <i class="ri-add-line"></i> Tambah Pelanggan
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

<x-card>
    <form method="GET" class="mb-6">
        <div class="flex gap-3 max-w-md">
            <x-input name="search" placeholder="Cari Kode / Nama / Telepon..." :value="request('search')" />
            <x-button type="submit" color="green">Cari</x-button>
        </div>
    </form>

    <x-table>
        <x-table-header>
            <tr class="text-left">
                <x-table-head>Kode</x-table-head>
                <x-table-head>Nama</x-table-head>
                <x-table-head>Alamat</x-table-head>
                <x-table-head>Telepon</x-table-head>
                <x-table-head class="text-center">Member</x-table-head>
                <x-table-head class="text-center">Status</x-table-head>
                <x-table-head class="text-center">Aksi</x-table-head>
            </tr>
        </x-table-header>
        <tbody>
            @forelse($customers as $customer)
                <tr class="hover:bg-slate-50/50">
                    <td class="p-3 font-mono text-xs text-slate-500">{{ $customer->kode_pelanggan }}</td>
                    <td class="p-3 font-semibold text-slate-800">{{ $customer->nama }}</td>
                    <td class="p-3 max-w-xs truncate text-slate-600">{{ $customer->alamat ?: '-' }}</td>
                    <td class="p-3 text-slate-600">{{ $customer->telepon ?: '-' }}</td>
                    <td class="p-3 text-center">
                        @if($customer->is_member)
                            <!-- Badges member diubah ke green/mint soft tint -->
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">Member</span>
                        @else
                            <span class="text-slate-400">-</span>
                        @endif
                    </td>
                    <td class="p-3 text-center">
                        @if($customer->status)
                            <x-badge color="green">Aktif</x-badge>
                        @else
                            <x-badge color="red">Nonaktif</x-badge>
                        @endif
                    </td>
                    <td class="p-3">
                        <div class="flex justify-center">
                            <a href="{{ route('customers.edit', $customer) }}">
                                <x-button size="sm" color="green">
                                    <i class="ri-edit-line"></i>
                                </x-button>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-empty-state icon="ri-user-3-line" title="Belum ada Pelanggan" description="Klik Tambah Pelanggan untuk mengisi data." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $customers->links() }}
    </div>
</x-card>
@endsection