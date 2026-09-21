@extends(
    preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', request()->header('User-Agent')) 
    || preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', request()->header('User-Agent')) 
    ? 'layouts.mobile-app' 
    : 'layouts.app'
)

@section('title', 'Detail Kartu Stok')

@section('content')

@php
    $currentStock = $productStock->stock ?? 0;
    $minStock = $productStock->min_stock ?? 0;
    $branchName = $productStock->branch->name ?? 'Semua Cabang';
    $isLow = $currentStock <= $minStock;
@endphp

<x-page-header
    title="Detail Kartu Stok"
    subtitle="Riwayat keluar masuk barang untuk {{ $product->name }} ({{ $branchName }})"
>
    <x-slot:action>
        <a href="{{ url()->previous() }}">
            <x-button color="gray" type="button">
                <i class="ri-arrow-left-line"></i>
                Kembali
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 mb-6">
    <x-card class="flex flex-col justify-between p-5">
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Kode Barang</span>
        <span class="text-lg font-bold text-slate-700 mt-1">{{ $product->sku ?: '-' }}</span>
    </x-card>
    
    <x-card class="flex flex-col justify-between p-5">
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama Produk / Cabang</span>
        <div class="mt-1">
            <span class="text-lg font-bold text-slate-700 block">{{ $product->name }}</span>
            <span class="text-xs font-medium text-emerald-600">
                <i class="ri-store-2-line"></i> {{ $branchName }}
            </span>
        </div>
    </x-card>

    <x-card class="flex flex-col justify-between p-5">
        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Stok Saat Ini</span>
        <div class="mt-1">
            <x-badge :color="$currentStock <= 0 ? 'red' : ($isLow ? 'yellow' : 'green')" size="md" class="font-bold">
                {{ number_format($currentStock) }} {{ $product->satuan }}
            </x-badge>
        </div>
    </x-card>
</div>

<x-card>
    <!-- TAMPILAN DESKTOP -->
    <div class="hidden md:block">
        <x-table>
            <x-table-header>
                <tr>
                    <x-table-head class="text-left">Tanggal</x-table-head>
                    <x-table-head class="text-right">Qty</x-table-head>
                    <x-table-head class="text-right">Sebelum</x-table-head>
                    <x-table-head class="text-right">Sesudah</x-table-head>
                    <x-table-head class="text-center">Tipe</x-table-head>
                    <x-table-head class="text-left">Referensi / Alasan</x-table-head>
                </tr>
            </x-table-header>

            <tbody>
            @forelse($movements as $row)
                <tr class="hover:bg-slate-50 transition-colors duration-150">
                    <x-table-cell class="text-left">
                        <span class="text-slate-600 font-medium">{{ $row->created_at->format('d M Y H:i') }}</span>
                    </x-table-cell>

                    <x-table-cell class="text-right font-bold">
                        @if($row->qty > 0)
                            <span class="text-emerald-600">+{{ $row->qty }}</span>
                        @else
                            <span class="text-red-600">{{ $row->qty }}</span>
                        @endif
                    </x-table-cell>

                    <x-table-cell class="text-right text-slate-500">
                        {{ $row->stock_before }}
                    </x-table-cell>

                    <x-table-cell class="text-right text-emerald-600 font-semibold">
                        {{ $row->stock_after }}
                    </x-table-cell>

                    <x-table-cell class="text-center">
                        @php
                            $badgeColor = ($row->qty > 0) ? ((strtolower($row->type) === 'opening') ? 'yellow' : 'green') : 'red';
                        @endphp
                        <x-badge :color="$badgeColor">
                            {{ $row->type }}
                        </x-badge>
                    </x-table-cell>

                    <x-table-cell class="text-left">
                        <div class="text-slate-700 font-medium">{{ $row->reference_no ?? '-' }}</div>
                        @if($row->notes)
                            <div class="text-xs text-slate-400 italic mt-0.5">{{ $row->notes }}</div>
                        @endif
                    </x-table-cell>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-empty-state
                            icon="ri-history-line"
                            title="Belum ada mutasi stok"
                            description="Seluruh riwayat perubahan stok produk ini untuk cabang ini akan tercatat di sini."
                        />
                    </td>
                </tr>
            @endforelse
            </tbody>
        </x-table>
    </div>

    <!-- TAMPILAN MOBILE & TABLET -->
    <div class="block md:hidden space-y-3">
        @forelse($movements as $row)
            @php
                $badgeColor = ($row->qty > 0) ? ((strtolower($row->type) === 'opening') ? 'yellow' : 'green') : 'red';
            @endphp
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 shadow-3xs space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-slate-500 font-medium"><i class="ri-calendar-line"></i> {{ $row->created_at->format('d M Y H:i') }}</span>
                    <x-badge :color="$badgeColor">{{ $row->type }}</x-badge>
                </div>

                <div class="flex justify-between items-center bg-white p-3 rounded-xl border border-slate-100">
                    <div>
                        <span class="text-xs text-slate-400 block">Perubahan</span>
                        <span class="text-base font-bold {{ $row->qty > 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $row->qty > 0 ? '+'.$row->qty : $row->qty }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-400 block">Stok Sblm → Ssh</span>
                        <span class="text-xs font-semibold text-slate-600">{{ $row->stock_before }} → <strong class="text-emerald-600">{{ $row->stock_after }}</strong></span>
                    </div>
                </div>

                <div class="text-xs text-slate-600">
                    <span class="font-semibold text-slate-700">Ref:</span> {{ $row->reference_no ?? '-' }}
                    @if($row->notes)
                        <p class="text-slate-400 italic mt-0.5">{{ $row->notes }}</p>
                    @endif
                </div>
            </div>
        @empty
            <x-empty-state
                icon="ri-history-line"
                title="Belum ada mutasi stok"
                description="Seluruh riwayat perubahan stok produk ini untuk cabang ini akan tercatat di sini."
            />
        @endforelse
    </div>
</x-card>

@endsection