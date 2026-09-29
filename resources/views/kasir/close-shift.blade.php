@extends(
    preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->header('User-Agent')) 
    || preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', request()->header('User-Agent')) 
    ? 'layouts.mobile-app' 
    : 'layouts.app'
)

@section('title', 'Tutup Shift Kasir')

@section('page_title', 'TUTUP SHIFT')
@section('page_subtitle', 'Finalisasi laporan harian')

@section('content')
<div class="max-w-2xl mx-auto p-0 md:p-8 md:px-4 pb-24">

    <!-- Header -->
    <div class="mb-8 border-b border-slate-200 pb-5 hidden md:block">
        <h1 class="text-3xl font-bold text-slate-800">Finalisasi & Tutup Shift</h1>
        <p class="text-sm text-slate-500 mt-1">Hitung dan masukkan total uang fisik di laci kasir secara teliti sebelum menutup sesi kerja.</p>
    </div>

    <div class="space-y-6">
        
        <!-- Info Sesi Shift -->
        <div class="bg-white rounded-xl shadow-xs border border-slate-100 p-5 md:p-6">
            <h3 class="text-sm md:text-base font-semibold text-slate-700 mb-4 flex items-center gap-2">
                <i class="ri-file-text-line text-lg text-green-600 font-normal"></i> Informasi Sesi Shift
            </h3>
            <div class="grid grid-cols-2 gap-x-3 gap-y-3 text-xs md:text-sm">
                <div>
                    <span class="text-slate-400 block">Operator Kasir</span>
                    <span class="text-slate-700 font-semibold text-base">{{ auth()->user()->name }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Waktu Mulai (Opened At)</span>
                    <span class="text-slate-700 font-semibold text-base">{{ $activeShift->opened_at->format('d M Y - H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Input Uang Fisik (Blind Closing) -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-base font-semibold text-slate-800 mb-2 flex items-center gap-2">
                <i class="ri-safe-2-line text-lg text-green-600 font-normal"></i> Input Uang Fisik Laci
            </h3>
            <p class="text-xs text-slate-500 mb-6">Hitung seluruh uang tunai yang ada di laci kasir (termasuk modal awal + hasil penjualan) dan masukkan jumlah akhirnya di bawah ini.</p>

            <form action="{{ route('kasir.store-close') }}" method="POST" id="formCloseShift">
                @csrf
                
                <div class="mb-6">
                    <label for="ending_cash_actual" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">
                        Total Uang Fisik Nyata (Rp)
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <span class="text-slate-400 font-semibold text-sm">Rp</span>
                        </div>
                        <input 
                            type="number" 
                            name="ending_cash_actual" 
                            id="ending_cash_actual" 
                            class="block w-full pl-12 pr-4 py-4 border-2 border-indigo-200 rounded-xl bg-indigo-50/20 text-slate-800 font-black focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 outline-none transition text-2xl text-center"
                            placeholder="0"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <!-- Tombol Submit Akhir -->
                <button 
                    type="submit" 
                    class="w-full bg-slate-800 hover:bg-slate-900 text-emerald-400 font-bold py-4 px-4 rounded-xl shadow-lg transition duration-200 text-sm uppercase tracking-wider flex items-center justify-center gap-2"
                >
                    <i class="ri-lock-2-line font-normal text-lg"></i> Kunci & Tutup Shift
                </button>
            </form>
        </div>

    </div>
</div>
@endsection