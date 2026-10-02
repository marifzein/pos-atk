@extends(
    preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->header('User-Agent')) 
    || preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', request()->header('User-Agent')) 
    ? 'layouts.mobile-app' 
    : 'layouts.app'
)

@section('title', 'Riwayat Kas Keluar')
@section('page_subtitle', 'Data audit pengeluaran kas kasir')

@section('content')

<div class="max-w-7xl mx-auto p-2 sm:p-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold mb-2 hidden md:block text-slate-800">
                Riwayat Kas Keluar
            </h1>
        </div>

        <!-- Tombol Aksi Export & Total Saldo Pengeluaran -->
        <div class="flex items-center gap-2">
            <div class="hidden sm:block bg-rose-50 border border-rose-200 px-3.5 py-1.5 rounded-lg text-right mr-1">
                <span class="text-[11px] font-semibold text-rose-500 uppercase tracking-wider block">Total Pengeluaran</span>
                <span class="text-sm font-bold text-rose-700 font-mono">Rp {{ number_format($totalNominal, 0, ',', '.') }}</span>
            </div>

            <a href="{{ route('laporan.pengeluaran.excel', request()->all()) }}" target="_blank"
               class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-3.5 rounded-lg text-sm transition flex items-center gap-1.5 shadow-sm">
                <i class="ri-file-excel-2-line"></i> Excel
            </a>
            <a href="{{ route('laporan.pengeluaran.pdf', request()->all()) }}" target="_blank"
               class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-3.5 rounded-lg text-sm transition flex items-center gap-1.5 shadow-sm">
                <i class="ri-file-pdf-line"></i> PDF
            </a>
        </div>
    </div>

    <!-- 🔍 FORM FILTER (UKURAN DAN KOMPONEN SESUAI HISTORY.BLADE.PHP) -->
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200/80 mb-6">
        <form method="GET" action="{{ route('laporan.pengeluaran.index') }}" class="grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
            
            <!-- Filter Cabang -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cabang</label>
                <select name="branch_id" id="filter-branch" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Cabang</option>
                    <?php foreach($branches as$b): ?>
                        <option value="{{ $b->id }}" {{ (string)request('branch_id') === (string)$b->id ? 'selected' : '' }}>
                            {{ $b->nama_cabang ?? $b->name }}
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Kasir (Dinamis Ajax) -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Kasir</label>
                <select name="user_id" id="filter-cashier" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Kasir</option>
                    <?php foreach($cashiers as$u): ?>
                        <option value="{{ $u->id }}" {{ (string)request('user_id') === (string)$u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter Kategori -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori</label>
                <select name="kategori" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua Kategori</option>
                    <option value="operasional" {{ request('kategori') == 'operasional' ? 'selected' : '' }}>Operasional</option>
                    <option value="teknisi_subkon" {{ request('kategori') == 'teknisi_subkon' ? 'selected' : '' }}>Teknisi/Subkon</option>
                    <option value="barang_supplier" {{ request('kategori') == 'barang_supplier' ? 'selected' : '' }}>Barang Supplier</option>
                    <option value="tarik_owner" {{ request('kategori') == 'tarik_owner' ? 'selected' : '' }}>Tarik Owner</option>
                    <option value="lain_lain" {{ request('kategori') == 'lain_lain' ? 'selected' : '' }}>Lain-lain</option>
                </select>
            </div>

            <!-- Rentang Tanggal: Dari Tgl -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tgl</label>
                <input 
                    type="date" 
                    name="start_date" 
                    value="{{ request('start_date') }}" 
                    class="w-full px-2.5 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
            </div>

            <!-- Rentang Tanggal: Sampai Tgl -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tgl</label>
                <input 
                    type="date" 
                    name="end_date" 
                    value="{{ request('end_date') }}" 
                    class="w-full px-2.5 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500"
                />
            </div>

            <!-- Tombol Aksi Filter & Reset -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2 px-4 rounded-lg text-sm transition flex items-center justify-center gap-1 shadow-sm">
                    <i class="ri-filter-3-line"></i> Filter
                </button>

                @if(request()->anyFilled(['branch_id', 'user_id', 'kategori', 'start_date', 'end_date']))
                    <a href="{{ route('laporan.pengeluaran.index') }}" class="px-3 py-2 bg-slate-200 text-slate-700 hover:bg-slate-300 rounded-lg text-sm font-medium transition">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TAMPILAN DESKTOP (TABEL SESUAI HISTORY.BLADE.PHP) --}}
    <div class="hidden md:block bg-white rounded-xl shadow-sm border border-slate-200/80 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200">
                    <th class="p-3.5 text-left">No Bukti</th>
                    <th class="p-3.5 text-left">Tanggal</th>
                    <th class="p-3.5 text-left">Kasir</th>
                    <th class="p-3.5 text-left">Kategori</th>
                    <th class="p-3.5 text-left">Penerima</th>
                    <th class="p-3.5 text-right">Jumlah</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (isset($expenses) &&$expenses->count() > 0): ?>
                    <?php foreach ($expenses as$item): ?>
                    <tr class="hover:bg-slate-50/70 transition">
                        <!-- Kolom No Bukti & Cabang di bawahnya -->
                        <td class="p-3.5 font-semibold text-slate-800">
                            <span class="block">{{ $item->no_bukti }}</span>
                            <span class="text-xs font-normal text-slate-500 block mt-0.5">
                                {{ $item->branch->nama_cabang ?? $item->branch->name ?? '-' }}
                            </span>
                        </td>

                        <!-- Kolom Tanggal & Jam di bawahnya -->
                        <td class="p-3.5">
                            <div class="font-medium text-slate-800">
                                {{ $item->created_at->translatedFormat('d F Y') }}
                            </div>
                            <div class="text-xs text-slate-400">
                                {{ $item->created_at->format('H:i') }} WIB
                            </div>
                        </td>

                        <!-- Kasir -->
                        <td class="p-3.5 text-slate-600">
                            {{ $item->user->name ?? '-' }}
                        </td>

                        <!-- Kategori (Badge Sesuai Standar Badge History) -->
                        <td class="p-3.5">
                            <span class="px-2.5 py-1 rounded-md text-xs font-semibold capitalize
                                @if($item->kategori === 'barang_supplier') bg-amber-50 text-amber-700
                                @elseif($item->kategori === 'operasional') bg-indigo-50 text-indigo-700
                                @elseif($item->kategori === 'tarik_owner') bg-purple-50 text-purple-700
                                @else bg-slate-100 text-slate-700 @endif">
                                {{ str_replace('_', ' ', $item->kategori) }}
                            </span>
                        </td>

                        <!-- Penerima & Keterangan -->
                        <td class="p-3.5 text-slate-800">
                            <div class="font-medium">{{ $item->penerima }}</div>
                            @if($item->catatan)
                                <div class="text-xs text-slate-400 mt-0.5">{{ $item->catatan }}</div>
                            @endif
                        </td>

                        <!-- Nominal Jumlah -->
                        <td class="p-3.5 text-right font-bold text-slate-800 font-mono">
                            Rp {{ number_format($item->nominal, 0, ',', '.') }}
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="p-10 text-center text-slate-400 italic">
                            Belum ada riwayat pengeluaran kas
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    {{-- TAMPILAN MOBILE & TABLET (CARD LIST SESUAI HISTORY.BLADE.PHP) --}}
    <div class="block md:hidden space-y-4 px-1">
        <?php if (isset($expenses) &&$expenses->count() > 0): ?>
            <?php foreach ($expenses as$item): ?>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm space-y-3 w-full">
                <div class="flex justify-between items-start border-b border-slate-100 pb-2.5">
                    <div>
                        <span class="text-base font-bold text-slate-900 block font-mono">{{ $item->no_bukti }}</span>
                        <span class="text-xs text-slate-500 font-medium flex items-center gap-1 mt-0.5">
                            <i class="ri-time-line"></i> {{ $item->created_at->translatedFormat('d F Y') }} ({{$item->created_at->format('H:i') }} WIB)
                        </span>
                    </div>
                    <div>
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold uppercase
                            @if($item->kategori === 'barang_supplier') bg-amber-100 text-amber-800
                            @elseif($item->kategori === 'operasional') bg-indigo-100 text-indigo-800
                            @else bg-slate-100 text-slate-800 @endif">
                            {{ str_replace('_', ' ', $item->kategori) }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                    <div class="col-span-2 border-b border-slate-200/60 pb-2 mb-1">
                        <span class="text-slate-400 block mb-0.5">Penerima:</span>
                        <span class="font-semibold text-slate-800 block">
                            {{ $item->penerima }}
                            @if($item->catatan)
                                <span class="text-slate-500 font-normal">({{ $item->catatan }})</span>
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Kasir:</span>
                        <span class="font-semibold text-slate-800 block truncate">{{ $item->user->name ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block mb-0.5">Cabang:</span>
                        <span class="font-semibold text-slate-800 block truncate">{{ $item->branch->nama_cabang ?? $item->branch->name ?? '-' }}</span>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 pt-1">
                    <div>
                        <span class="text-xs text-slate-400 block">Jumlah Kas Keluar</span>
                        <span class="font-black text-lg text-rose-600 font-mono">
                            Rp {{ number_format($item->nominal, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-white rounded-2xl p-8 text-center text-slate-400 italic border border-slate-200">
                Belum ada riwayat pengeluaran kas
            </div>
        <?php endif; ?>
    </div>

    {{-- PAGINATION --}}
    @if(method_exists($expenses, 'links'))
        <div class="mt-4">
            {{ $expenses->links() }}
        </div>
    @endif
</div>

<!-- Script Dinamis Filter Kasir Berdasarkan Cabang -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchSelect = document.getElementById('filter-branch');
    const cashierSelect = document.getElementById('filter-cashier');
    const selectedCashierId = "{{ request('user_id') }}";

    branchSelect.addEventListener('change', function () {
        const branchId = this.value || 'all';
        cashierSelect.innerHTML = '<option value="">Memuat kasir...</option>';

        fetch(`/api/cashiers-by-branch/${branchId}`)
            .then(res => res.json())
            .then(data => {
                cashierSelect.innerHTML = '<option value="">Semua Kasir</option>';
                data.forEach(user => {
                    const opt = document.createElement('option');
                    opt.value = user.id;
                    opt.textContent = user.name;
                    if (user.id == selectedCashierId) {
                        opt.selected = true;
                    }
                    cashierSelect.appendChild(opt);
                });
            })
            .catch(() => {
                cashierSelect.innerHTML = '<option value="">Semua Kasir</option>';
            });
    });
});
</script>
@endsection