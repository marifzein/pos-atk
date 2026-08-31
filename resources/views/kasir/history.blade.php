@extends(
    preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->header('User-Agent')) 
    || preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', request()->header('User-Agent')) 
    ? 'layouts.mobile-app' 
    : 'layouts.app'
)

@section('title','Riwayat Transaksi')
@section('page_subtitle', 'Data penjualan kasir')

@section('content')

<div class="max-w-7xl mx-auto p-2 sm:p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold mb-2 hidden md:block text-slate-800">
            Riwayat Transaksi
        </h1>
    </div>

    <!-- 🔍 FORM FILTER SEARCH & RENTANG TANGGAL -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200/80 mb-6">
        <form method="GET" action="{{ route('kasir.history') }}" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            
            <!-- Cari No Nota -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">No. Nota</label>
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Cari No. Nota..." 
                        class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="ri-search-line"></i>
                    </div>
                </div>
            </div>

            <!-- Cari Nama Pelanggan -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Pelanggan</label>
                <div class="relative">
                    <input 
                        type="text" 
                        name="customer_name" 
                        value="{{ request('customer_name') }}" 
                        placeholder="Cari Pelanggan..." 
                        class="w-full pl-9 pr-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
                    />
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i class="ri-user-search-line"></i>
                    </div>
                </div>
            </div>

            <!-- Rentang Tanggal -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tgl</label>
                    <input 
                        type="date" 
                        name="date_from" 
                        value="{{ request('date_from') }}" 
                        class="w-full px-2.5 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tgl</label>
                    <input 
                        type="date" 
                        name="date_to" 
                        value="{{ request('date_to') }}" 
                        class="w-full px-2.5 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                    />
                </div>
            </div>

            <!-- Tombol Aksi Filter -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition flex items-center justify-center gap-1 shadow-sm">
                    <i class="ri-filter-3-line"></i> Filter
                </button>

                @if(request()->anyFilled(['search', 'customer_name', 'date_from', 'date_to']))
                    <a href="{{ route('kasir.index') }}" class="px-3 py-2 bg-slate-200 text-slate-700 hover:bg-slate-300 rounded-lg text-sm font-medium transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TAMPILAN DESKTOP (TABEL SESUAI LAMPIRAN) --}}
    <div class="hidden md:block bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                    <th class="p-3.5 text-left">No Nota</th>
                    <th class="p-3.5 text-left">Tanggal</th>
                    <th class="p-3.5 text-left">Kasir</th>
                    <th class="p-3.5 text-left">Pelanggan</th>
                    <th class="p-3.5 text-center">Status</th>
                    <th class="p-3.5 text-right">Total</th>
                    <th class="p-3.5 text-center w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($transactions as $trx)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="p-3.5 font-semibold text-slate-800">
                            {{ $trx->no_nota }}
                        </td>
                        <td class="p-3.5 text-slate-600 font-mono text-xs">
                            {{ $trx->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="p-3.5 text-slate-600">
                            {{ $trx->cashier?->name ?? 'System' }}
                        </td>
                        <td class="p-3.5">
                            @if($trx->customer)
                                <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-md text-xs font-semibold">
                                    {{ $trx->customer->nama }}
                                </span>
                            @else
                                <span class="text-slate-400 text-xs italic">Umum (Non-Member)</span>
                            @endif
                        </td>
                        <td class="p-3.5 text-center">
                            @if(strtoupper($trx->status) === 'BATAL')
                                <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-md text-xs font-bold uppercase">
                                    Batal
                                </span>
                            @else
                                <span class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-md text-xs font-bold uppercase">
                                    SOLD
                                </span>
                            @endif
                        </td>
                        <td class="p-3.5 text-right font-bold text-slate-800 font-mono">
                            Rp {{ number_format($trx->grand_total, 0, ',', '.') }}
                        </td>
                        <td class="p-3.5 text-center">
                            <a href="{{ route('kasir.show', $trx->id) }}" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow-sm transition">
                                <i class="ri-printer-line"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-10 text-center text-slate-400 italic">
                            Belum ada riwayat transaksi
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- TAMPILAN MOBILE & TABLET (CARD LIST) --}}
    <div class="block md:hidden space-y-4 px-1">
        @forelse($transactions as $trx)
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm space-y-3 w-full">
                <div class="flex justify-between items-start border-b border-slate-100 pb-2.5">
                    <div>
                        <span class="text-base font-bold text-slate-900 block font-mono">{{ $trx->no_nota }}</span>
                        <span class="text-xs text-slate-500 font-medium flex items-center gap-1 mt-0.5">
                            <i class="ri-time-line"></i> {{ $trx->created_at->format('Y-m-d H:i:s') }}
                        </span>
                    </div>
                    <div>
                        @if(strtoupper($trx->status) === 'BATAL')
                            <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-lg text-xs font-bold uppercase">
                                Batal
                            </span>
                        @else
                            <span class="bg-emerald-100 text-emerald-800 px-2.5 py-1 rounded-lg text-xs font-bold uppercase">
                                SOLD
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 block mb-0.5">Kasir:</span>
                        <span class="font-semibold text-slate-800 block truncate">{{ $trx->cashier?->name ?? 'System' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Pelanggan:</span>
                        @if($trx->customer)
                            <span class="font-semibold text-indigo-600 block truncate">{{ $trx->customer->nama }}</span>
                        @else
                            <span class="text-slate-400 italic block">Umum</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 pt-1">
                    <div>
                        <span class="text-xs text-slate-400 block">Total Transaksi</span>
                        <span class="font-black text-lg text-slate-900 font-mono">
                            Rp {{ number_format($trx->grand_total, 0, ',', '.') }}
                        </span>
                    </div>
                    <a href="{{ route('kasir.show', $trx->id) }}" class="inline-flex items-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-sm transition">
                        <i class="ri-printer-line text-sm"></i> Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl p-8 text-center text-slate-400 italic border border-slate-200">
                Belum ada riwayat transaksi
            </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    @if(method_exists($transactions, 'links'))
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    @endif
</div>

@endsection