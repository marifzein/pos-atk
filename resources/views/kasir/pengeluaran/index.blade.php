@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    <!-- Header Bersih (Tanpa Informasi Total Saldo) -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Kas Keluar Kasir</h1>
            <p class="text-xs text-slate-500 mt-1">Pencatatan bukti uang keluar dari laci kasir (Operasional, Teknisi, & Pembayaran Barang)</p>
        </div>
        <div class="bg-slate-50 border border-slate-200 px-4 py-2 rounded-xl text-right">
            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Status Shift</span>
            <span class="text-xs font-bold text-emerald-600 flex items-center gap-1.5 justify-end mt-0.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Shift Aktif
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- FORM INPUT KAS KELUAR (5 Kolom) -->
        <div class="lg:col-span-5 bg-white p-5 rounded-2xl shadow-sm border border-slate-100">
            <h2 class="text-sm font-bold text-slate-700 mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                <i class="ri-money-cny-circle-line text-emerald-600 text-lg"></i> Form Pengeluaran
            </h2>

            <form action="{{ route('kasir.pengeluaran.store') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Kategori -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori Biaya</label>
                    <select name="kategori" id="kategori-select" required
                        class="w-full text-xs rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="operasional">Beban Operasional (Listrik, Sampah, Galon)</option>
                        <option value="teknisi_subkon">Teknisi / Subkon (Servis, Ongkos Jahit)</option>
                        <option value="barang_supplier">Pembayaran Barang / COD Supplier</option>
                        <option value="tarik_owner">Tarik Tunai / Setor ke Brankas/Owner</option>
                        <option value="lain_lain">Lain-lain</option>
                    </select>
                </div>

                <!-- Bagian Pilih Dokumen Penerimaan Barang (Kondisional) -->
                <div id="wrapper-penerimaan" class="hidden p-3 bg-amber-50 rounded-xl border border-amber-200 space-y-2">
                    <label class="block text-xs font-semibold text-amber-900">Tarik Tagihan Penerimaan Barang</label>
                    <select name="penerimaan_barang_id" id="penerimaan-select"
                        class="w-full text-xs rounded-lg border-amber-300 bg-white focus:border-amber-500 focus:ring-amber-500">
                        <option value="">-- Pilih Dokumen Penerimaan --</option>
                    </select>
                    <p class="text-[10px] text-amber-700 leading-tight">Menampilkan surat penerimaan barang yang belum lunas di cabang ini.</p>
                </div>

                <!-- Penerima -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Penerima Uang</label>
                    <input type="text" name="penerima" id="penerima-input" required placeholder="Misal: Kurir J&T COD / Pak Wagiman Listrik"
                        class="w-full text-xs rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <!-- Nominal (Input uang yang dikeluarkan saat itu saja) -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nominal Diserahkan (Rp)</label>
                    <input type="number" name="nominal" id="nominal-input" required min="1" placeholder="0"
                        class="w-full text-sm font-bold text-slate-800 rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500">
                </div>

                <!-- Catatan -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Keterangan / Catatan</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan tambahan..."
                        class="w-full text-xs rounded-lg border-slate-200 bg-slate-50 focus:bg-white focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                </div>

                <button type="submit" 
                    class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl shadow-md shadow-rose-500/20 transition flex items-center justify-center gap-2">
                    <i class="ri-arrow-up-circle-line text-base"></i> Simpan Bukti Pengeluaran
                </button>
            </form>
        </div>

        <!-- TABEL BUKTI TRANSAKSI PENGELUARAN (7 Kolom) -->
        <div class="lg:col-span-7 bg-white p-5 rounded-2xl shadow-sm border border-slate-100 flex flex-col justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-700 mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                    <span><i class="ri-history-line text-emerald-600 text-lg mr-1"></i> Bukti Pengeluaran Kas Terinput</span>
                    <span class="text-xs font-normal text-slate-400">Shift Berjalan</span>
                </h2>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-400">
                                <th class="pb-2">No Bukti</th>
                                <th class="pb-2">Waktu</th>
                                <th class="pb-2">Kategori</th>
                                <th class="pb-2">Penerima</th>
                                <th class="pb-2 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @if(isset($expenses) && $expenses->isNotEmpty())
                                @foreach($expenses as $item)
                                <tr>
                                    <td class="py-2.5 font-semibold text-slate-700">
                                        {{ $item->no_bukti }}
                                    </td>
                                    <td class="py-2.5 text-slate-500">
                                        {{ $item->created_at->format('H:i') }} WIB
                                    </td>
                                    <td class="py-2.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold capitalize
                                            @if($item->kategori === 'barang_supplier') bg-amber-100 text-amber-700
                                            @elseif($item->kategori === 'operasional') bg-blue-100 text-blue-700
                                            @elseif($item->kategori === 'tarik_owner') bg-purple-100 text-purple-700
                                            @else bg-slate-100 text-slate-600 @endif">
                                            {{ str_replace('_', ' ', $item->kategori) }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 text-slate-600">
                                        <div class="font-medium">{{ $item->penerima }}</div>
                                        @if($item->catatan)<div class="text-[10px] text-slate-400 truncate max-w-[150px]">{{ $item->catatan }}</div>@endif
                                    </td>
                                    <td class="py-2.5 text-center">
                                        <form action="{{ route('kasir.pengeluaran.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Hapus catatan pengeluaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-slate-400 hover:text-rose-600 transition" title="Hapus jika salah input">
                                                <i class="ri-delete-bin-line text-sm"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">
                                        Belum ada pengeluaran kas tercatat pada shift ini.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const kategoriSelect = document.getElementById('kategori-select');
    const wrapperPenerimaan = document.getElementById('wrapper-penerimaan');
    const penerimaanSelect = document.getElementById('penerimaan-select');
    const penerimaInput = document.getElementById('penerima-input');
    const nominalInput = document.getElementById('nominal-input');

    let listUnpaid = [];

    kategoriSelect.addEventListener('change', function () {
        if (this.value === 'barang_supplier') {
            wrapperPenerimaan.classList.remove('hidden');
            loadUnpaidPenerimaan();
        } else {
            wrapperPenerimaan.classList.add('hidden');
            penerimaanSelect.innerHTML = '<option value="">-- Pilih Dokumen Penerimaan --</option>';
        }
    });

    function loadUnpaidPenerimaan() {
        penerimaanSelect.innerHTML = '<option value="">Memuat tagihan...</option>';
        fetch("{{ route('kasir.api.unpaid_penerimaan') }}")
            .then(res => res.json())
            .then(data => {
                listUnpaid = data;
                penerimaanSelect.innerHTML = '<option value="">-- Pilih Dokumen Penerimaan --</option>';
                if (data.length === 0) {
                    penerimaanSelect.innerHTML = '<option value="">(Tidak ada tagihan tertunggak)</option>';
                    return;
                }
                data.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = `${item.no_penerimaan} | ${item.supplier_name}`;
                    penerimaanSelect.appendChild(opt);
                });
            });
    }

    penerimaanSelect.addEventListener('change', function () {
        const selected = listUnpaid.find(i => i.id == this.value);
        if (selected) {
            penerimaInput.value = selected.supplier_name + ' (Ref: ' + selected.ref_doc + ')';
            nominalInput.value = selected.sisa_tagihan;
        }
    });
});
</script>
@endsection