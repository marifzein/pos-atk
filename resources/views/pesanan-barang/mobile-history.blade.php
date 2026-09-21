@extends('layouts.mobile-app')

@section('title', 'Riwayat Pesanan Barang')
@section('page_subtitle', 'Daftar riwayat pesanan barang')

@section('content')
<div x-data="historyBarangMobile()" class="space-y-3 pb-24">

    <!-- 1. TOOLBAR CARI & FILTER -->
    <div class="bg-white rounded-2xl p-3 shadow-xs border border-slate-100">
        <form method="GET" action="{{ route('pesanan-barang.history') }}" class="space-y-2">
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <i class="ri-search-2-line text-lg"></i>
                    </span>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="No WO / Pelanggan..." 
                        class="w-full bg-slate-50 border-0 focus:ring-2 focus:ring-indigo-500 text-xs font-bold rounded-xl pl-9 pr-3 py-2.5 outline-none"
                    >
                </div>
                <button type="submit" class="bg-indigo-600 active:bg-indigo-700 text-white font-bold text-xs px-3.5 py-2.5 rounded-xl transition flex items-center gap-1 shrink-0">
                    <i class="ri-filter-3-line text-sm"></i> Filter
                </button>
            </div>

            @if(request()->hasAny(['search', 'customer_id', 'sort_by']))
                <div class="pt-1 flex justify-end">
                    <a href="{{ route('pesanan-barang.history') }}" class="text-[11px] font-bold text-rose-500 hover:underline">
                        <i class="ri-refresh-line"></i> Reset Filter
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- 2. TOMBOL BUAT PESANAN BARU -->
    <a href="{{ route('pesanan-barang.index') }}" class="w-full bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 text-indigo-700 font-bold text-xs py-2.5 px-3 rounded-xl flex items-center justify-center gap-1.5 transition active:scale-98">
        <i class="ri-add-circle-line text-base"></i> Buat Pesanan Barang Baru
    </a>

    <!-- 3. LIST CARD TRANSAKSI MOBILE -->
    <div class="space-y-2.5">
        @forelse($orders as $order)
            @php
                $firstItem = $order->items->first();
                $firstItemName = $firstItem ? $firstItem->item_name : '-';
                $otherItemsCount = $order->items->count() - 1;
                $totalNominal = $order->orderItems?->sum('subtotal') ?? $order->items?->sum('subtotal') ?? 0;
            @endphp

            <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-100 space-y-2.5">
                
                <!-- Header Card: No WO + Status Badge -->
                <div class="flex justify-between items-start border-b border-slate-100 pb-2">
                    <div>
                        <div class="font-black text-slate-800 text-sm font-mono tracking-tight">
                            {{ $order->no_pesanan }}
                        </div>
                        <div class="text-[10px] font-semibold text-slate-400 mt-0.5">
                            <i class="ri-building-4-line text-slate-300"></i> {{ $order->branch->name ?? 'Pusat' }} • {{ $order->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div>
                        @if(strtolower($order->status) === 'batal')
                            <span class="bg-rose-50 text-rose-600 border border-rose-100 text-[10px] font-black px-2 py-0.5 rounded-md">BATAL</span>
                        @elseif(strtolower($order->status) === 'lunas')
                            <span class="bg-emerald-50 text-emerald-600 border border-emerald-100 text-[10px] font-black px-2 py-0.5 rounded-md">LUNAS</span>
                        @else
                            <span class="bg-amber-50 text-amber-600 border border-amber-100 text-[10px] font-black px-2 py-0.5 rounded-md">ORDER</span>
                        @endif
                    </div>
                </div>

                <!-- Body Card: Pelanggan, Item, Catatan -->
                <div class="space-y-1 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-400 font-medium">Pelanggan:</span>
                        <span class="font-bold text-slate-800">
                            {{ $order->customer?->nama ?? ($order->customer_name_manual ?? 'Umum (Non-Member)') }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-slate-400 font-medium">Item Barang:</span>
                        <span class="font-bold text-slate-700 text-right truncate max-w-[180px]">
                            {{ \Illuminate\Support\Str::limit($firstItemName, 20) }}
                            @if($otherItemsCount > 0)
                                <span class="bg-slate-100 text-slate-600 text-[9px] font-bold px-1 rounded">+{{ $otherItemsCount }}</span>
                            @endif
                        </span>
                    </div>

                    @if($order->catatan)
                        <div class="bg-slate-50 p-2 rounded-lg text-[11px] text-slate-500 italic border border-slate-100">
                            "{{ \Illuminate\Support\Str::limit($order->catatan, 50) }}"
                        </div>
                    @endif
                </div>

                <!-- Footer Card: Total & Tombol Aksi -->
                <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                    <div>
                        <span class="text-[9px] font-bold text-slate-400 uppercase block">Total Biaya</span>
                        <span class="text-sm font-black text-indigo-600 font-mono">Rp {{ number_format($totalNominal, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('pesanan-jasa.show', $order->id) }}" class="bg-emerald-600 text-white font-bold text-xs px-3 py-1.5 rounded-xl transition active:scale-95 flex items-center gap-1 shadow-2xs">
                            <i class="ri-file-text-line"></i> Detail
                        </a>

                        @if(strtolower($order->status) == 'order')
                            <button type="button" @click="openModal('{{ $order->id }}', '{{ $order->no_pesanan }}')" class="bg-rose-50 text-rose-600 font-bold text-xs px-2.5 py-1.5 rounded-xl border border-rose-100 active:scale-95 transition">
                                Batal
                            </button>
                        @endif
                    </div>
                </div>

            </div>
        @empty
            <div class="text-center py-12 text-slate-400 italic text-xs bg-white rounded-2xl border border-dashed border-slate-200">
                <i class="ri-history-line text-3xl text-slate-300 block mb-1"></i>
                Belum ada riwayat pesanan barang.
            </div>
        @endforelse
    </div>

    <!-- PAGINASI MOBILE -->
    <div class="mt-4">
        {{ $orders->links() }}
    </div>

    <!-- MODAL PEMBATALAN PESANAN BARANG -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4" @keydown.escape.window="showModal = false">
        <div class="bg-white rounded-2xl p-4 w-full max-w-sm shadow-2xl" @click.outside="showModal = false">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-black text-slate-800 text-sm flex items-center gap-1 text-rose-600">
                    <i class="ri-error-warning-line text-lg"></i> Pembatalan Pesanan
                </h3>
                <button type="button" @click="showModal = false" class="w-7 h-7 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center font-bold">✕</button>
            </div>

            <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-100 mb-3 text-xs">
                <span class="text-slate-400 block font-bold text-[10px] uppercase">No. Work Order</span>
                <span class="font-black text-slate-800 font-mono" x-text="selectedNoWO"></span>
            </div>

            <form @submit.prevent="submitBatal()">
                <div class="space-y-2.5">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Alasan Pembatalan</label>
                        <textarea x-model="alasan" rows="2" required placeholder="Masukan alasan pembatalan..." class="w-full bg-slate-50 border-0 focus:ring-2 focus:ring-rose-500 rounded-xl p-2.5 text-xs outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1">Password Otorisasi</label>
                        <input type="password" x-model="password" required placeholder="Masukan password anda" class="w-full bg-slate-50 border-0 focus:ring-2 focus:ring-rose-500 rounded-xl p-2.5 text-xs outline-none">
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" @click="showModal = false" class="px-3.5 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold">Batal</button>
                    <button type="submit" :disabled="loading" class="px-4 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold flex items-center gap-1 shadow-sm active:scale-95 transition">
                        <template x-if="loading"><i class="ri-loader-4-line animate-spin"></i></template>
                        <span>Proses Batal</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function historyBarangMobile() {
    return {
        showModal: false,
        selectedOrderId: null,
        selectedNoWO: '',
        alasan: '',
        password: '',
        loading: false,

        openModal(id, noWO) {
            this.selectedOrderId = id;
            this.selectedNoWO = noWO;
            this.alasan = '';
            this.password = '';
            this.showModal = true;
        },

        async submitBatal() {
            if (!this.alasan || !this.password) return;
            this.loading = true;

            try {
                let response = await fetch("{{ url('/pesanan-barang') }}/" + this.selectedOrderId + "/batal", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        alasan: this.alasan,
                        password: this.password
                    })
                });

                let result = await response.json();

                if (response.ok && result.success) {
                    this.showModal = false;
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: result.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(() => { location.reload(); }, 1500);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: result.message || 'Terjadi kesalahan sistem.'
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan Sistem',
                    text: 'Tidak dapat menghubungkan ke server.'
                });
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush