@extends(
    preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->header('User-Agent')) 
    || preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', request()->header('User-Agent')) 
    ? 'layouts.mobile-app' 
    : 'layouts.app'
)

@section('title', 'Laporan Penerimaan Kas Kasir')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <x-card class="bg-white shadow rounded-lg p-6">
        
        <!-- Header & Judul -->
        <div class="flex items-center justify-between mb-4 sm:mb-6">
            <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                <i class="ri-wallet-3-line text-indigo-600"></i> Laporan Penerimaan Kas Kasir (Audit Kasir)
            </h2>
        </div>

        <!-- Filter Tanggal, Cabang, dan Tombol Export -->
        <form method="GET" action="{{ route('laporan.penjualan-kasir') }}" class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 sm:p-5 mb-6 no-print">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 items-end">
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Dari Tanggal</label>
                    <input type="date" name="dari_tanggal" value="{{ $dari_tanggal }}" 
                        class="rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full text-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Sampai Tanggal</label>
                    <input type="date" name="sampai_tanggal" value="{{ $sampai_tanggal }}" 
                        class="rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full text-sm">
                </div>

                <!-- FILTER CABANG -->
                @if(in_array(strtolower(Auth::user()->role), ['owner', 'admin', 'developer']))
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Cabang</label>
                    <select name="branch_id" class="rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full text-sm">
                        <option value="">Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $selectedBranchId == $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                
                <!-- INPUT FILTER NAMA KASIR -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Kasir</label>
                    <input type="text" name="kasir" value="{{ $kasir ?? '' }}" placeholder="Semua Kasir..."
                        class="rounded-xl border border-slate-300 px-4 py-2.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 w-full text-sm">
                </div>

                <!-- TOMBOL ACTION (EXPORT & FILTER) -->
                <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap justify-between items-center gap-2 pt-2 border-t border-slate-200/60">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('laporan.penjualan-kasir.excel', request()->all()) }}" 
                           class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-600 text-white font-semibold rounded-xl hover:bg-emerald-700 transition shadow-xs text-sm">
                            <i class="ri-file-excel-2-line text-base"></i> Export Excel
                        </a>
                        <button type="button" onclick="window.print()" 
                           class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-slate-700 text-white font-semibold rounded-xl hover:bg-slate-800 transition shadow-xs text-sm">
                            <i class="ri-printer-line text-base"></i> Cetak / PDF
                        </button>
                    </div>

                    <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition duration-150 text-sm">
                        Tampilkan
                    </button>
                </div>
            </div>
        </form>

        <!-- Tabel Laporan Desktop -->
        <!-- Tabel Laporan Desktop -->
        <div class="hidden md:block overflow-x-auto">
            <x-table class="border-collapse w-full">
                <x-table-header>
                    <tr class="border-b border-slate-200 bg-slate-50 text-slate-700 text-xs">
                        <th class="py-3 px-3 text-center w-12 border-r border-slate-200">No</th>
                        <th class="py-3 px-3 text-left border-r border-slate-200">Tanggal</th>
                        <th class="py-3 px-3 text-left border-r border-slate-200">Cabang</th>
                        <th class="py-3 px-3 text-left border-r border-slate-200">Nama Kasir</th>
                        <th class="py-3 px-3 text-center border-r border-slate-200">Jml Trx</th>
                        <th class="py-3 px-3 text-right text-indigo-700 border-r border-slate-200">Modal Awal</th>
                        <th class="py-3 px-3 text-right border-r border-slate-200">Cash Masuk</th>
                        <th class="py-3 px-3 text-right text-rose-600 border-r border-slate-200">Pengeluaran Kas</th>
                        <th class="py-3 px-3 text-right font-bold text-slate-800 bg-slate-100 border-r border-slate-200">Saldo Kasir</th>
                        <th class="py-3 px-3 text-right border-r border-slate-200">Card / QRIS / TF</th>
                        <th class="py-3 px-3 text-right text-amber-600 border-r border-slate-200">Voucher</th>
                        <th class="py-3 px-3 text-right font-bold text-emerald-600">Total Setoran</th>
                    </tr>
                </x-table-header>
                
                <x-table-body>
                    @forelse ($reports as $report)
                        <x-table-row>
                            <x-table-cell class="text-center border-r border-slate-200">{{ ($reports->currentPage() - 1) * $reports->perPage() + $loop->iteration }}</x-table-cell>
                            <x-table-cell class="border-r border-slate-200">{{ \Carbon\Carbon::parse($report->tanggal)->translatedFormat('d F Y') }}</x-table-cell>
                            <x-table-cell class="font-medium text-slate-700 border-r border-slate-200">{{ $report->nama_cabang ?? '-' }}</x-table-cell>
                            <x-table-cell class="font-medium text-slate-900 border-r border-slate-200">{{ $report->nama_kasir }}</x-table-cell>
                            <x-table-cell class="text-center font-semibold text-indigo-600 border-r border-slate-200">
                                {{ $report->jumlah_transaksi ?? 0 }}
                            </x-table-cell>
                            <x-table-cell class="text-right text-indigo-700 border-r border-slate-200">Rp {{ number_format($report->total_modal_awal, 0, ',', '.') }}</x-table-cell>
                            <x-table-cell class="text-right border-r border-slate-200">Rp {{ number_format($report->total_cash_masuk, 0, ',', '.') }}</x-table-cell>
                            <x-table-cell class="text-right text-rose-600 border-r border-slate-200">Rp {{ number_format($report->total_expense, 0, ',', '.') }}</x-table-cell>
                            <x-table-cell class="text-right font-bold text-slate-800 bg-slate-50 border-r border-slate-200">Rp {{ number_format($report->saldo_kasir, 0, ',', '.') }}</x-table-cell>
                            <x-table-cell class="text-right border-r border-slate-200">Rp {{ number_format($report->total_card, 0, ',', '.') }}</x-table-cell>
                            <x-table-cell class="text-right text-amber-600 border-r border-slate-200">Rp {{ number_format($report->total_voucher, 0, ',', '.') }}</x-table-cell>
                            <x-table-cell class="text-right font-bold text-emerald-600">Rp {{ number_format($report->total_grand, 0, ',', '.') }}</x-table-cell>
                        </x-table-row>
                    @empty
                        <x-table-row>
                            <x-table-cell colspan="12" class="text-center py-8 text-slate-400 italic">
                                Tidak ada data penerimaan kas pada rentang tanggal ini.
                            </x-table-cell>
                        </x-table-row>
                    @endforelse
                </x-table-body>

                <!-- Bagian Total Footer -->
                @if($reports->count() > 0)
                    <tfoot class="bg-slate-100 border-t-2 border-slate-300 font-bold text-slate-800 text-xs">
                        <tr>
                            <td class="px-3 py-4 text-center border-r border-b border-slate-200" colspan="4">TOTAL PERIODE INI</td>
                            <td class="px-3 py-4 text-center text-indigo-700 border-r border-b border-slate-200">
                                {{ $totals->total_transaksi ?? 0 }}
                            </td>
                            <td class="px-2 py-4 text-right text-indigo-700 border-r border-b border-slate-200">Rp {{ number_format($totals->total_modal_awal ?? 0, 0, ',', '.') }}</td>
                            <td class="px-2 py-4 text-right text-indigo-700 border-r border-b border-slate-200">Rp {{ number_format($totals->total_cash_masuk ?? 0, 0, ',', '.') }}</td>
                            <td class="px-2 py-4 text-right text-rose-600 border-r border-b border-slate-200">Rp {{ number_format($totals->total_expense ?? 0, 0, ',', '.') }}</td>
                            <td class="px-2 py-4 text-right text-slate-900 bg-slate-200 border-r border-b border-slate-200">Rp {{ number_format($totals->total_saldo_kasir ?? 0, 0, ',', '.') }}</td>
                            <td class="px-2 py-4 text-right text-indigo-700 border-r border-b border-slate-200">Rp {{ number_format($totals->total_card ?? 0, 0, ',', '.') }}</td>
                            <td class="px-2 py-4 text-right text-amber-600 border-r border-slate-200">Rp {{ number_format($totals->total_voucher ?? 0, 0, ',', '.') }}</td>
                            <td class="px-2 py-4 text-right text-emerald-700 text-sm border-b border-slate-200">Rp {{ number_format($totals->total_grand ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </x-table>
        </div>

        <!-- TAMPILAN MOBILE & TABLET (CARD LIST) -->
        {{-- <div class="grid grid-cols-4 gap-2 bg-white p-2.5 rounded-xl border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-400 block">Modal Awal</span>
                            <span class="font-semibold text-indigo-600">Rp {{ number_format($report->total_modal_awal, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Cash Masuk</span>
                            <span class="font-semibold text-slate-700">Rp {{ number_format($report->total_cash_masuk, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Kas Keluar</span>
                            <span class="font-semibold text-rose-600">Rp {{ number_format($report->total_expense, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block font-bold text-slate-700">Saldo Kasir</span>
                            <span class="font-bold text-slate-900">Rp {{ number_format($report->saldo_kasir, 0, ',', '.') }}</span>
                        </div>
                    </div>
        </div> --}}

        <!-- Pagination Links -->
        <div class="mt-5 no-print">
            {{ $reports->links() }}
        </div>

    </x-card>
</div>

<style>
@media print {
    .no-print, aside, header, footer, nav {
        display: none !important;
    }
    body {
        background: white !important;
        font-size: 11px !important;
    }
    .max-w-7xl {
        max-width: 100% !important;
        padding: 0 !important;
    }
    table {
        width: 100% !important;
        border: 1px solid #000 !important;
    }
    th, td {
        border: 1px solid #000 !important;
        padding: 4px 6px !important;
    }
}
</style>
@endsection