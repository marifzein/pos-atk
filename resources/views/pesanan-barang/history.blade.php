@extends('layouts.app')

@section('title', 'Riwayat Pesanan Barang')

@section('content')
<div x-data="historyBarang()">

<x-page-header title="Riwayat Pesanan Barang (Sales Order)" subtitle="Daftar riwayat transaksi pemesanan barang">
    <x-slot:action>
        <a href="{{ route('pesanan-barang.index') }}">
            <x-button color="primary">
                <i class="ri-add-line"></i> Buat Pesanan Baru
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

@php
    $getSortLink = function($column) use ($sortBy, $sortDir) {
        $nextDir = ($sortBy === $column && $sortDir === 'asc') ? 'desc' : 'asc';
        return request()->fullUrlWithQuery([
            'sort_by'  => $column,
            'sort_dir' => $nextDir,
            'page'     => 1
        ]);
    };
    
    $renderArrow = function($column) use ($sortBy, $sortDir) {
        if ($sortBy !== $column) return '';
        return $sortDir === 'asc' ? ' ▲' : ' ▼';
    };
@endphp

<x-card>
    <div class="flex flex-wrap justify-between items-center mb-6 gap-3">
        <form method="GET" action="{{ route('pesanan-barang.history') }}" class="flex flex-wrap gap-3 items-center w-full md:w-auto">
            <x-search-box name="search" :value="request('search')" placeholder="Cari No Nota / Pelanggan..." />

            <x-select name="customer_id" class="w-52">
                <option value="">Semua Pelanggan</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>
                        {{ $customer->nama }}
                    </option>
                @endforeach
            </x-select>

            <x-button color="gray" type="submit">
                <i class="ri-filter-3-line"></i> Filter
            </x-button>

            @if(request()->hasAny(['search', 'customer_id', 'sort_by']))
                <a href="{{ route('pesanan-barang.history') }}">
                    <x-button color="secondary" type="button">Reset</x-button>
                </a>
            @endif
        </form>
    </div>

    <x-table>
        <x-table-header>
            <tr>
                <x-table-head class="p-3 text-left font-bold"><a href="{{ $getSortLink('no_pesanan') }}">No SO {{ $renderArrow('no_pesanan') }}</a></x-table-head>
                <x-table-head class="p-3 text-left font-bold"><a href="{{ $getSortLink('created_at') }}">Tanggal {{ $renderArrow('created_at') }}</a></x-table-head>
                <x-table-head class="p-3 text-left font-bold">Operator</x-table-head>
                <x-table-head class="p-3 text-left font-bold">Pelanggan</x-table-head>
                <x-table-head class="p-3 text-center font-bold">Status</x-table-head>
                <x-table-head class="p-3 text-right font-bold">Total</x-table-head>
                <x-table-head class="p-3 text-center font-bold">Aksi</x-table-head>
            </tr>
        </x-table-header>

        <tbody>
            @forelse($orders as $order)
                <tr class="hover:bg-slate-50/50 transition">
                    <td class="p-3 font-semibold text-slate-800 font-mono text-sm">{{ $order->no_pesanan }}</td>
                    <td class="p-3 text-slate-500 text-xs">{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                    <td class="p-3 text-slate-700 font-medium text-sm">{{ $order->operator->name ?? 'Admin' }}</td>
                    <td class="p-3 text-slate-700 text-sm">
                        @if($order->customer)
                            <span class="font-semibold text-indigo-900">{{ $order->customer->nama }}</span>
                        @else
                            <span class="text-slate-400 italic">{{ $order->customer_name_manual ?? 'Umum (Non-Member)' }}</span>
                        @endif
                    </td>
                    <td class="p-3 text-center">
                        @if($order->status === 'batal')
                            <x-badge color="red">Batal</x-badge>
                        @elseif($order->status === 'lunas')
                            <x-badge color="green">LUNAS</x-badge>
                        @else
                            <x-badge color="yellow">ORDER</x-badge>
                        @endif
                    </td>
                    <td class="p-3 text-right font-bold text-slate-900 font-mono">
                        Rp {{ number_format($order->orderItems?->sum('subtotal') ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="p-3 text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('pesanan-barang.show', $order->id) }}" class="inline-flex items-center justify-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-xs px-2.5 py-1 rounded-md transition shadow-sm">
                                <i class="ri-file-text-line"></i> Detail
                            </a>
                            @if(strtolower($order->status) !== 'batal')
                                <button type="button" @click="openModal('{{ $order->id }}', '{{ $order->no_pesanan }}')" class="inline-flex items-center px-2.5 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-medium rounded-md shadow-sm transition">
                                    <i class="ri-close-circle-line mr-1"></i> Batal
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        <x-empty-state icon="ri-history-line" title="Belum ada riwayat pesanan" description="Riwayat pesanan barang akan muncul di sini." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-6">
        {{ $orders->links() }}
    </div>
</x-card>

<!-- Modal Pembatalan -->
<div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" x-cloak>
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 border border-slate-100" @click.outside="showModal = false">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-slate-800">Pembatalan Pesanan Barang</h3>
            <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600"><i class="ri-close-line text-xl"></i></button>
        </div>
        <p class="text-xs text-slate-500 mb-4">No. SO: <span class="font-bold text-slate-800" x-text="selectedNoSO"></span></p>

        <form @submit.prevent="submitBatal()">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Alasan Pembatalan :</label>
                    <textarea x-model="alasan" rows="3" required placeholder="Masukan alasan pembatalan..." class="w-full border border-slate-300 rounded-xl p-3 text-sm outline-none"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 mb-1">Password Otorisasi :</label>
                    <input type="password" x-model="password" required placeholder="Masukan password anda" class="w-full border border-slate-300 rounded-xl p-3 text-sm outline-none">
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="showModal = false" class="px-4 py-2.5 rounded-xl border text-slate-600 font-semibold text-sm">Batal</button>
                <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold text-sm transition">Proses Pembatalan</button>
            </div>
        </form>
    </div>
</div>

</div>

<script>
function historyBarang() {
    return {
        showModal: false,
        selectedOrderId: null,
        selectedNoSO: '',
        alasan: '',
        password: '',
        loading: false,

        openModal(id, noSO) {
            this.selectedOrderId = id;
            this.selectedNoSO = noSO;
            this.alasan = '';
            this.password = '';
            this.showModal = true;
        },

        async submitBatal() {
            if (!this.alasan || !this.password) return;
            this.loading = true;
            try {
                let response = await fetch( "{{ url(`/pesanan-barang/${this.selectedOrderId}/batal` ) }}" , {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ alasan: this.alasan, password: this.password })
                });

                let result = await response.json();
                if (response.ok && result.success) {
                    this.showModal = false;
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.message, timer: 2000, showConfirmButton: false });
                    setTimeout(() => { location.reload(); }, 1500);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: result.message || 'Terjadi kesalahan.' });
                }
            } catch (error) {
                Swal.fire({ icon: 'error', title: 'Sistem Error', text: 'Tidak dapat terhubung ke server.' });
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endsection