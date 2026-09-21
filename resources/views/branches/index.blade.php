@extends('layouts.app')

@section('title', 'Master Cabang')

@section('content')
<x-page-header title="Master Cabang" subtitle="Kelola data Cabang / Outlet">
    <x-slot:action>
        <a href="{{ route('branches.create') }}">
            <x-button color="green">
                <i class="ri-add-line"></i> Tambah Cabang
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
                <x-table-head>Nama Cabang</x-table-head>
                <x-table-head>Telepon</x-table-head>
                <x-table-head>Alamat</x-table-head>
                <x-table-head class="text-center">Status</x-table-head>
                <x-table-head class="text-center">Aksi</x-table-head>
            </tr>
        </x-table-header>
        <tbody>
            @forelse($branches as $branch)
                <tr class="hover:bg-slate-50/50">
                    <td class="p-3 font-semibold text-slate-700">
                        <span class="px-2 py-1 bg-slate-100 rounded text-xs border border-slate-200">
                            {{ $branch->code }}
                        </span>
                    </td>
                    <td class="p-3 font-semibold text-slate-800">{{ $branch->name }}</td>
                    <td class="p-3 text-slate-600">{{ $branch->phone ?: '-' }}</td>
                    <td class="p-3 max-w-xs truncate text-slate-600">{{ $branch->address ?: '-' }}</td>
                    <td class="p-3 text-center">
                        @if($branch->is_active)
                            <x-badge color="green">Aktif</x-badge>
                        @else
                            <x-badge color="red">Nonaktif</x-badge>
                        @endif
                    </td>
                    <td class="p-3">
                        <div class="flex justify-center">
                            <a href="{{ route('branches.edit', $branch) }}">
                                <x-button size="sm" color="green">
                                    <i class="ri-edit-line"></i>
                                </x-button>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-empty-state icon="ri-store-3-line" title="Belum ada Cabang" description="Klik Tambah Cabang untuk mengkonfigurasi outlet baru." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $branches->links() }}
    </div>
</x-card>
@endsection