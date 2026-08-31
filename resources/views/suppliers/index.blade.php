@extends('layouts.app')

@section('title', 'Master Supplier')

@section('content')
<x-page-header title="Master Supplier" subtitle="Kelola data Supplier / Vendor">
    <x-slot:action>
        <a href="{{ route('suppliers.create') }}">
            <x-button color="green">
                <i class="ri-add-line"></i> Tambah Supplier
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

<x-card>
    <form method="GET" class="mb-6">
        <div class="flex gap-3 max-w-md">
            <x-input name="search" placeholder="Cari Nama / PIC / Telepon..." :value="request('search')" />
            <x-button type="submit" color="green">Cari</x-button>
        </div>
    </form>

    <x-table>
        <x-table-header>
            <tr class="text-left">
                <x-table-head>Nama Supplier</x-table-head>
                <x-table-head>PIC</x-table-head>
                <x-table-head>Telepon</x-table-head>
                <x-table-head>Email</x-table-head>
                <x-table-head>Alamat</x-table-head>
                <x-table-head class="text-center">Status</x-table-head>
                <x-table-head class="text-center">Aksi</x-table-head>
            </tr>
        </x-table-header>
        <tbody>
            @forelse($suppliers as $supplier)
                <tr class="hover:bg-slate-50/50">
                    <td class="p-3 font-semibold text-slate-800">{{ $supplier->name }}</td>
                    <td class="p-3 text-slate-600">{{ $supplier->pic ?: '-' }}</td>
                    <td class="p-3 text-slate-600">{{ $supplier->phone ?: '-' }}</td>
                    <td class="p-3 text-slate-600">{{ $supplier->email ?: '-' }}</td>
                    <td class="p-3 max-w-xs truncate text-slate-600">{{ $supplier->address ?: '-' }}</td>
                    <td class="p-3 text-center">
                        @if($supplier->is_active)
                            <x-badge color="green">Aktif</x-badge>
                        @else
                            <x-badge color="red">Nonaktif</x-badge>
                        @endif
                    </td>
                    <td class="p-3">
                        <div class="flex justify-center">
                            <a href="{{ route('suppliers.edit', $supplier) }}">
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
                        <x-empty-state icon="ri-store-2-line" title="Belum ada Supplier" description="Klik Tambah Supplier untuk mengisi data mitra kerja." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $suppliers->links() }}
    </div>
</x-card>
@endsection