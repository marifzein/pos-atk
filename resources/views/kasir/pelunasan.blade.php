@extends('layouts.app')

@section('title', 'Pelunasan SP - ' . ($branchName ))

@section('content')

<x-page-header 
    title="Pelunasan Surat Pesanan (SP) {{ $branchName }}" 
    subtitle="Pengambilan barang & pelunasan sisa tagihan pesanan inden"
    subtitleColor="text-amber-600"
>
    <x-slot:action>
        <a href="{{ route('kasir.index') }}">
            <x-button color="gray" type="button">
                <i class="ri-arrow-left-line"></i> Kembali
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

<div class="max-w-7xl mx-auto p-4" x-data="pelunasanSp()">
    <div class="grid grid-cols-12 gap-4">

        <!-- ==================== AREA KIRI (INFORMASI ORDER TERKUNCI) ==================== -->
        <div class="col-span-8 space-y-4">
            
            <!-- INFORMASI DOKUMEN -->
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex justify-between items-start border-b pb-4 mb-4">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">No. Surat Pesanan (SP)</span>
                        <h2 class="text-2xl font-black text-indigo-950 font-mono mt-0.5">{{ $order->no_pesanan }}</h2>
                        <span class="text-xs text-slate-500 mt-1 block">Tgl Order: {{ $order->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Pelanggan</span>
                        <div class="text-lg font-bold text-slate-800">{{ $order->customer->nama ?? $order->customer_name_manual ?? 'Umum' }}</div>
                        <div class="text-xs text-slate-500">{{ $order->customer->telepon ?? '-' }}</div>
                    </div>
                </div>

                @if($order->catatan)
                <div class="bg-amber-50/70 border border-amber-200 rounded-lg p-3 mb-4">
                    <span class="text-xs font-bold text-amber-800 uppercase block">Catatan Pengerjaan:</span>
                    <p class="text-xs text-amber-950 mt-0.5">{{ $order->catatan }}</p>
                </div>
                @endif

                <!-- TABEL RINCIAN ITEM (READONLY) -->
                <div>
                    <h3 class="font-bold text-sm text-slate-700 uppercase mb-2">Item Pekerjaan / Barang</h3>
                    <div class="border rounded-xl overflow-hidden">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 text-slate-600 text-xs font-semibold border-b">
                                    <th class="p-3 text-center w-12">No</th>
                                    <th class="p-3 text-left">Nama Item & Spesifikasi</th>
                                    <th class="p-3 text-center w-24">Qty</th>
                                    <th class="p-3 text-right w-36">Harga Satuan</th>
                                    <th class="p-3 text-right w-40">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($order->items as $idx => $item)
                                <tr>
                                    <td class="p-3 text-center text-slate-500 font-medium bg-gray-50/50">{{ $idx + 1 }}</td>
                                    <td class="p-3 font-semibold text-slate-800">
                                        {{ $item->item_name }}
                                        @if($item->notes)
                                            <div class="text-xs text-slate-500 font-normal italic mt-0.5">{{ $item->notes }}</div>
                                        @endif
                                    </td>
                                    <td class="p-3 text-center font-bold font-mono">{{ $item->qty }}</td>
                                    <td class="p-3 text-right font-mono text-slate-600">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- RIWAYAT PEMBAYARAN DP SEBELUMNYA -->
            <div class="bg-white rounded-xl shadow p-5">
                <h3 class="font-bold text-sm text-slate-700 uppercase mb-3">Riwayat Uang Muka (DP) Masuk</h3>
                @if($order->payments->count() > 0)
                    <div class="space-y-2">
                        @foreach($order->payments as $pay)
                        <div class="flex justify-between items-center p-3 bg-slate-50 rounded-lg border border-slate-200">
                            <div>
                                <span class="font-mono font-bold text-xs text-indigo-700">{{ $pay->no_bukti_bayar }}</span>
                                <div class="text-xs text-slate-500">{{ $pay->created_at->format('d/m/Y H:i') }} • Kasir: {{ $pay->cashier->name ?? 'Kasir' }}</div>
                                <div class="text-xs text-slate-600 uppercase font-semibold mt-0.5">Metode: {{ $pay->metode_pembayaran }} {{ $pay->bank_name ? "($pay->bank_name)" : '' }}</div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-slate-500 block">Nominal DP</span>
                                <span class="font-mono font-bold text-emerald-600 text-base">Rp {{ number_format($pay->nominal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-4 bg-slate-50 rounded-lg text-center text-xs text-slate-500 italic border">
                        Pesanan ini dibuat tanpa Uang Muka (DP = Rp 0).
                    </div>
                @endif
            </div>

        </div>

        <!-- ==================== AREA KANAN (PANEL PELUNASAN KASIR) ==================== -->
        <div class="col-span-4">
            {{-- <div class="bg-white rounded-xl shadow p-5 space-y-4 sticky top-4">
                
                <h3 class="text-lg font-bold border-b pb-2 text-slate-800 tracking-wide">
                    FORM PELUNASAN
                </h3>

                <!-- Ringkasan Angka -->
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Total Kontrak Order</span>
                        <span class="font-mono font-bold text-slate-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-emerald-600">
                        <span>Sudah Dibayar (DP)</span>
                        <span class="font-mono font-bold">- Rp {{ number_format($totalDp, 0, ',', '.') }}</span>
                    </div>
                    <hr class="border-slate-100">
                    <div class="flex justify-between items-center bg-rose-50 p-3 rounded-xl border border-rose-100">
                        <span class="font-bold text-rose-800 text-sm">SISA WAJIB LUNAS</span>
                        <span class="font-black text-xl text-rose-600 font-mono">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Pilihan Metode Pembayaran -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase">Metode Pelunasan</label>
                    <div class="grid grid-cols-4 gap-1 text-xs">
                        <button type="button" @click="metode = 'cash'" :class="metode === 'cash' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="py-2 rounded-lg border transition text-center">Cash</button>
                        <button type="button" @click="metode = 'qris'" :class="metode === 'qris' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="py-2 rounded-lg border transition text-center">QRIS</button>
                        <button type="button" @click="metode = 'card'" :class="metode === 'card' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="py-2 rounded-lg border transition text-center">Card</button>
                        <button type="button" @click="metode = 'transfer'" :class="metode === 'transfer' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="py-2 rounded-lg border transition text-center">Transfer</button>
                    </div>
                </div>

                <!-- Input Non-Tunai Tambahan -->
                <div x-show="metode !== 'cash'" x-cloak class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-0.5">Bank / E-Wallet</label>
                        <input type="text" x-model="bankName" placeholder="BCA / BRI" class="w-full text-xs rounded border border-slate-300 p-1.5 outline-none focus:border-indigo-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-600 mb-0.5">No. Ref / Approval</label>
                        <input type="text" x-model="refNo" placeholder="Kode trx" class="w-full text-xs rounded border border-slate-300 p-1.5 outline-none focus:border-indigo-500 bg-white">
                    </div>
                </div>

                <!-- Input Uang Diterima -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase">Nominal Bayar Pelunasan (F4)</label>
                    <input 
                        type="number" 
                        id="nominalBayarInput"
                        x-model.number="nominalBayar" 
                        @input="recalculate()"
                        class="w-full text-right font-mono font-bold text-xl rounded-xl border border-slate-300 p-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                    >
                    <div class="flex gap-1.5 mt-1.5">
                        <button type="button" @click="setPas()" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 py-1.5 px-3 rounded-lg border font-medium flex-1">Uang Pas</button>
                    </div>
                </div>

                <!-- Kembalian -->
                <div class="flex justify-between items-center text-sm font-bold pt-2 border-t">
                    <span class="text-slate-600">Kembalian</span>
                    <span class="font-mono text-lg text-emerald-600" x-text="'Rp ' + formatRupiah(kembalian)"></span>
                </div>

                <!-- Tombol Submit -->
                <button 
                    type="button" 
                    @click="submitPelunasan()" 
                    :disabled="nominalBayar < sisaWajib"
                    :class="nominalBayar >= sisaWajib ? 'bg-indigo-600 hover:bg-indigo-700 cursor-pointer' : 'bg-slate-300 cursor-not-allowed'"
                    class="w-full text-white py-3.5 rounded-xl font-bold shadow-md transition flex items-center justify-center gap-2 text-base mt-2"
                >
                    <i class="ri-check-double-line text-xl"></i>
                    <span>Lunas & Cetak Nota (NP)</span>
                </button>

            </div> --}}
            <!-- FORM PELUNASAN / PENYERAHAN BARANG -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-5 space-y-4">
                <h3 class="font-bold text-slate-800 text-base border-b pb-2">
                    {{ $sisaTagihan <= 0 ? 'PENYERAHAN BARANG' : 'FORM PELUNASAN' }}
                </h3>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Total Kontrak Order</span>
                        <span class="font-semibold text-slate-800 font-mono">Rp {{ number_format($totalKontrak, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-emerald-600">
                        <span>Sudah Dibayar (DP / Full)</span>
                        <span class="font-semibold font-mono">- Rp {{ number_format($totalDp, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Kotak Status Sisa Tagihan -->
                <div class="p-3.5 rounded-xl border {{ $sisaTagihan <= 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200' }} flex justify-between items-center">
                    <span class="font-bold text-xs uppercase tracking-wide {{ $sisaTagihan <= 0 ? 'text-emerald-800' : 'text-rose-800' }}">
                        {{ $sisaTagihan <= 0 ? 'STATUS PEMBAYARAN' : 'SISA WAJIB LUNAS' }}
                    </span>
                    <span class="font-extrabold font-mono text-lg {{ $sisaTagihan <= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                        {{ $sisaTagihan <= 0 ? 'LUNAS (Rp 0)' : 'Rp ' . number_format($sisaTagihan, 0, ',', '.') }}
                    </span>
                </div>

                @if($sisaTagihan <= 0)
                    {{-- KONDISI 1: JIKA SUDAH LUNAS (HANYA AMBIL BARANG & TERBITKAN NP) --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3.5 text-xs text-slate-600 space-y-2">
                        <div class="flex items-center gap-2 font-bold text-emerald-700">
                            <i class="ri-checkbox-circle-fill text-base"></i> Tagihan Sudah Dibayar Penuh di Awal
                        </div>
                        <p>
                            Pelanggan telah melunasi tagihan saat Surat Pesanan diterbitkan. Tidak ada uang yang perlu disetor atau dihitung kembali ke laci kasir saat ini.
                        </p>
                    </div>

                    <!-- Tombol Khusus Selesai & Cetak NP -->
                    <button 
                        type="button" 
                        onclick="submitPelunasanLunas()" 
                        class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-sm cursor-pointer mt-4"
                    >
                        <i class="ri-printer-line text-lg"></i>
                        <span>Proses & Terbitkan Nota (NP)</span>
                    </button>

                @else
                    {{-- KONDISI 2: JIKA MASIH ADA SISA TAGIHAN (FORM INPUT AKTIF) --}}
                    <div class="space-y-4">
                        <!-- Pilihan Metode -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase">Metode Pelunasan</label>
                            <div class="grid grid-cols-4 gap-1.5 text-xs">
                                <button type="button" @click="metode = 'cash'" :class="metode === 'cash' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700'" class="py-2 rounded-lg border transition">Cash</button>
                                <button type="button" @click="metode = 'qris'" :class="metode === 'qris' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700'" class="py-2 rounded-lg border transition">QRIS</button>
                                <button type="button" @click="metode = 'card'" :class="metode === 'card' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700'" class="py-2 rounded-lg border transition">Card</button>
                                <button type="button" @click="metode = 'transfer'" :class="metode === 'transfer' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700'" class="py-2 rounded-lg border transition">Transfer</button>
                            </div>
                        </div>

                        <!-- Input Nominal Bayar -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1 uppercase">Nominal Bayar Pelunasan (F4)</label>
                            <input 
                                type="number" 
                                x-model.number="nominalBayar" 
                                @input="calculateKembalian()" 
                                class="w-full text-right rounded-xl border border-slate-300 p-2.5 font-mono font-bold text-slate-800 text-lg outline-none focus:border-indigo-500"
                            >
                            <button type="button" @click="nominalBayar = {{ $sisaTagihan }}; calculateKembalian()" class="w-full mt-1.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold">
                                Uang Pas
                            </button>
                        </div>

                        <div class="flex justify-between items-center text-sm font-semibold border-t pt-2">
                            <span class="text-slate-600">Kembalian</span>
                            <span class="font-mono text-emerald-600 text-base" x-text="formatRupiah(kembalian)"></span>
                        </div>

                        <button 
                            type="button" 
                            @click="submitPelunasan()" 
                            class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-2 text-sm cursor-pointer"
                        >
                            <i class="ri-check-double-line text-lg"></i>
                            <span>F10 - Lunas & Cetak Nota (NP)</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>

<script>
function pelunasanSp() {
    return {
        sisaWajib: {{ $sisaTagihan }},
        nominalBayar: {{ $sisaTagihan }}, // default uang pas
        kembalian: 0,
        metode: 'cash',
        bankName: '',
        refNo: '',

        init() {
            this.recalculate();
            window.addEventListener('keydown', (e) => {
                if (e.key === 'F4') {
                    e.preventDefault();
                    document.getElementById('nominalBayarInput')?.focus();
                    document.getElementById('nominalBayarInput')?.select();
                } else if (e.key === 'F10') {
                    e.preventDefault();
                    this.submitPelunasan();
                }
            });
        },

        setPas() {
            this.nominalBayar = this.sisaWajib;
            this.recalculate();
        },

        recalculate() {
            this.kembalian = Math.max(0, Number(this.nominalBayar || 0) - this.sisaWajib);
        },

        formatRupiah(value) {
            return Number(value || 0).toLocaleString('id-ID');
        },

        async submitPelunasan() {
            if (this.nominalBayar < this.sisaWajib) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pembayaran Kurang',
                    text: 'Sisa tagihan (Rp ' + this.formatRupiah(this.sisaWajib) + ')'
                });
                return;
            }

            const konfirmasi = await Swal.fire({
                title: 'Selesaikan Pelunasan?',
                text: 'Pesanan akan ditutup lunas dan Nota Penjualan (NP) akan diterbitkan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Selesaikan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#4f46e5'
            });

            if (!konfirmasi.isConfirmed) return;

            try {
                let response = await fetch("{{ route('kasir.pelunasan.store', $order->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        nominal_bayar: this.nominalBayar,
                        metode_pembayaran: this.metode,
                        bank_name: this.bankName,
                        ref_no: this.refNo
                    })
                });

                let result = await response.json();

                if (result.success) {
                    Swal.fire({
                        title: 'Pelunasan Berhasil!',
                        text: 'No Nota: ' + result.no_nota,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    setTimeout(() => {
                        // Buka struk pelunasan di tab baru, lalu kembalikan kasir ke antrean
                        window.open("{{ url('/kasir/pelunasan') }}/" + result.transaction_id + "/print", '_blank');
                        window.location.href = "{{ route('kasir.index') }}";
                    }, 1200);
                } else {
                    Swal.fire('Gagal', result.message || 'Terjadi kesalahan sistem', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Gagal memproses pelunasan ke server', 'error');
            }
        }
    }
}


function submitPelunasanLunas() {
    Swal.fire({
        title: 'Serahkan Barang & Terbitkan NP?',
        text: 'Pesanan sudah berstatus lunas. Sistem akan menerbitkan Nota Penjualan (NP) dan menyelesaikan transaksi.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Terbitkan NP',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#059669'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                let response = await fetch("{{ route('kasir.pelunasan.store', $order->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        nominal_bayar: 0,
                        metode_pembayaran: 'cash'
                    })
                });

                let res = await response.json();

                if (res.success) {
                    // Cetak nota pelunasan final
                    window.open(`{{ url('/kasir/pelunasan') }}/${res.transaction_id}/print`, '_blank');
                    
                    await Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: `Nota Penjualan ${res.no_nota} berhasil diterbitkan.`,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    window.location.href = "{{ route('kasir.index') }}";
                } else {
                    Swal.fire('Gagal', res.message, 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        }
    });
}

</script>

@endsection 