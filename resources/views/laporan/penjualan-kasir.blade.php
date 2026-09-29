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
        <div class="hidden md:block">
            <x-table class="border-collapse">
                <x-table-header>
                    <tr class="border-b border-slate-200">
                        <x-table-head class="text-center w-12 font-bold bg-slate-100 text-slate-700 border-r border-slate-200" rowspan="2">No</x-table-head>
                        <x-table-head class="text-left font-bold bg-slate-100 text-slate-700 border-r border-slate-200" rowspan="2">Tanggal</x-table-head>
                        <x-table-head class="text-left font-bold bg-slate-100 text-slate-700 border-r border-slate-200" rowspan="2">Cabang</x-table-head>
                        <x-table-head class="text-left font-bold bg-slate-100 text-slate-700 border-r border-slate-200" rowspan="2">Nama Kasir</x-table-head>
                        <x-table-head class="text-center font-bold bg-slate-100 text-slate-700 border-r border-slate-200" rowspan="2">Jml Trx</x-table-head>
                        <x-table-head class="text-center font-bold bg-slate-100 text-slate-700 border-b border-slate-200" colspan="4">Penerimaan Uang (Kas Masuk)</x-table-head>
                    </tr>
                    <tr class="border-b border-slate-200">
                        <x-table-head class="text-right font-bold bg-slate-50 text-slate-700 w-44 border-r border-slate-200">Uang Cash</x-table-head>
                        <x-table-head class="text-right font-bold bg-slate-50 text-slate-700 w-44 border-r border-slate-200">Card / QRIS / TF</x-table-head>
                        <x-table-head class="text-right font-bold bg-slate-50 text-slate-700 w-44 border-r border-slate-200">Voucher</x-table-head>
                        <x-table-head class="text-right font-bold bg-slate-50 text-slate-700 w-44">Total Masuk</x-table-head>
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
                                {{ $report->jumlah_transaksi ?? $report->jumlah_nota ?? 0 }}
                            </x-table-cell>
                            <x-table-cell class="text-right border-r border-slate-200">Rp {{ number_format($report->total_cash, 0, ',', '.') }}</x-table-cell>
                            <x-table-cell class="text-right border-r border-slate-200">Rp {{ number_format($report->total_card, 0, ',', '.') }}</x-table-cell>
                            <x-table-cell class="text-right border-r border-slate-200">Rp {{ number_format($report->total_voucher, 0, ',', '.') }}</x-table-cell>
                            <x-table-cell class="text-right font-semibold text-slate-900">Rp {{ number_format($report->total_grand, 0, ',', '.') }}</x-table-cell>
                        </x-table-row>
                    @empty
                        <x-table-row>
                            <x-table-cell colspan="9" class="text-center py-8 text-slate-400 italic">
                                Tidak ada data penerimaan kas pada rentang tanggal ini.
                            </x-table-cell>
                        </x-table-row>
                    @endforelse
                </x-table-body>

                <!-- Bagian Total Footer -->
                @if($reports->count() > 0)
                    <tfoot class="bg-slate-100 border-t-2 border-slate-300 font-bold text-slate-800 text-sm">
                        <tr>
                            <td class="px-3 py-4 text-center border-r border-b border-slate-200" colspan="4">TOTAL PERIODE INI</td>
                            <td class="px-3 py-4 text-center text-indigo-700 border-r border-b border-slate-200">
                                {{ $totals->total_transaksi ?? $totals->total_nota ?? 0 }}
                            </td>
                            <td class="px-2 py-4 text-right text-indigo-700 border-r border-b border-slate-200">Rp {{ number_format($totals->total_cash ?? 0, 0, ',', '.') }}</td>
                            <td class="px-2 py-4 text-right text-indigo-700 border-r border-b border-slate-200">Rp {{ number_format($totals->total_card ?? 0, 0, ',', '.') }}</td>
                            <td class="px-2 py-4 text-right text-indigo-700 border-r border-b border-slate-200">Rp {{ number_format($totals->total_voucher ?? 0, 0, ',', '.') }}</td>
                            <td class="px-2 py-4 text-right text-green-700 text-base border-b border-slate-200">Rp {{ number_format($totals->total_grand ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </x-table>
        </div>

        <!-- TAMPILAN MOBILE & TABLET (CARD LIST) -->
        <div class="block md:hidden space-y-4">
            @if($reports->count() > 0)
                <div class="bg-slate-700 text-white p-4 rounded-2xl shadow-sm space-y-2">
                    <span class="text-xs font-medium uppercase tracking-wider text-indigo-200">TOTAL PERIODE INI</span>
                    <div class="text-2xl font-bold text-green-400">
                        Rp {{ number_format($totals->total_grand ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-500 text-xs">
                        <div>
                            <span class="text-indigo-200 block">Cash</span>
                            <span class="font-semibold">Rp {{ number_format($totals->total_cash ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-indigo-200 block">Card/QRIS</span>
                            <span class="font-semibold">Rp {{ number_format($totals->total_card ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-indigo-200 block">Voucher</span>
                            <span class="font-semibold">Rp {{ number_format($totals->total_voucher ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @forelse ($reports as $report)
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 shadow-3xs space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-xs font-semibold text-slate-500 block"><i class="ri-calendar-line"></i> {{ \Carbon\Carbon::parse($report->tanggal)->translatedFormat('d F Y') }}</span>
                            <h4 class="font-bold text-slate-800 text-base mt-0.5"><i class="ri-user-3-line text-indigo-600"></i> {{ $report->nama_kasir }}</h4>
                            <span class="text-xs text-slate-600 font-medium block mt-0.5"><i class="ri-store-2-line"></i> {{ $report->nama_cabang ?? '-' }}</span>
                            <span class="inline-block mt-1 px-2 py-0.5 text-xs bg-indigo-50 text-indigo-700 rounded-md font-semibold">
                                {{ $report->jumlah_transaksi ?? $report->jumlah_nota ?? 0 }} Transaksi
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-400 block">Total Masuk</span>
                            <span class="font-bold text-green-600 text-base">Rp {{ number_format($report->total_grand, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 bg-white p-2.5 rounded-xl border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-400 block">Cash</span>
                            <span class="font-semibold text-slate-700">Rp {{ number_format($report->total_cash, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Card/QRIS</span>
                            <span class="font-semibold text-slate-700">Rp {{ number_format($report->total_card, 0, ',', '.') }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block">Voucher</span>
                            <span class="font-semibold text-slate-700">Rp {{ number_format($report->total_voucher, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 italic bg-slate-50 rounded-2xl border border-slate-200/80">
                    Tidak ada data penerimaan kas pada rentang tanggal ini.
                </div>
            @endforelse
        </div>

        <!-- Pagination Links -->
        <div class="mt-5 no-print">
            {{ $reports->links() }}
        </div>

    </x-card>
</div>

{{-- CSS KHUSUS PRINT CETAK / PDF --}}
<style>
@media print {
    .no-print, aside, header, footer, nav {
        display: none !important;
    }
    body {
        background: white !important;
        font-size: 12px !important;
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
        padding: 4px 8px !important;
    }
}
</style>
@endsection