@extends('layouts.app')

@section('title', 'Kasir - Daftar Pesanan Jasa')

@section('content')

{{-- gridjs bos --}}
{{-- <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />
<script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script> --}}

<link href="{{ asset('css/gridjs/mermaid.min.css') }}" rel="stylesheet" />
<script src="{{ asset('js/gridjs/gridjs.umd.js') }}"></script>

<style>
    .gridjs-wrapper { border-radius: 0.5rem; border: none !important; box-shadow: none !important; }
    .gridjs-head { background-color: #f8fafc; }
    th.gridjs-th { background-color: #f8fafc !important; color: #475569 !important; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; padding: 12px 16px !important; }
    td.gridjs-td { padding: 12px 16px !important; border-bottom: 1px solid #f1f5f9 !important; font-size: 0.875rem; }
    .gridjs-footer { border-top: 1px solid #f1f5f9 !important; background-color: transparent !important; padding: 12px 0 0 0 !important; }
    .gridjs-pagination .gridjs-pages button.gridjs-currentPage { background-color: #4f46e5 !important; color: white !important; border-color: #4f46e5 !important; }
</style>



<x-page-header title="POS / Kasir" subtitle="Daftar pesanan yang siap diproses menjadi Nota">
    
</x-page-header>



<!-- Container Card Utama -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-5">
    <div id="gridjs-table"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new gridjs.Grid({
        columns: [
            { 
                name: 'No WO',
                formatter: (cell) => gridjs.html(`<span class="font-bold text-slate-800 font-mono text-base">${cell}</span>`)
            },
            { name: 'Tanggal' },
            { name: 'Operator' },
            { 
                name: 'Pelanggan',
                formatter: (cell) => gridjs.html(`<span class="font-semibold text-slate-700">${cell}</span>`)
            },
            { 
                name: 'Status',
                attributes: { class: 'text-center' },
                formatter: () => gridjs.html(`<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">ORDER</span>`)
            },
            { 
                name: 'Total',
                attributes: { class: 'text-right' },
                formatter: (cell) => gridjs.html(`<span class="font-bold text-slate-900 font-mono">${cell}</span>`)
            },
            { 
                name: 'Aksi',
                sort: false,
                attributes: { class: 'text-center' },
                formatter: (url) => gridjs.html(`
                    <a href="${url}" class="inline-flex items-center justify-center gap-1 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-lg transition shadow-xs text-xs font-semibold">
                        <i class="ri-money-dollar-box-line"></i> Bayar
                    </a>
                `)
            }
        ],
        server: {
            url: '{{ route("kasir.api.orders") }}',
            then: data => data.data,
            total: data => data.total
        },
        search: {
            server: {
                url: (prev, keyword) => `${prev}?search=${encodeURIComponent(keyword)}`
            }
        },
        sort: {
            multiColumn: false,
            server: {
                url: (prev, columns) => {
                    if (!columns.length) return prev;
                    const col = columns[0];
                    const dir = col.direction === 1 ? 'asc' : 'desc';
                    const delimiter = prev.includes('?') ? '&' : '?';
                    return `${prev}${delimiter}sort=${dir}`;
                }
            }
        },
        pagination: {
            limit: 10,
            server: {
                url: (prev, page, limit) => {
                    const delimiter = prev.includes('?') ? '&' : '?';
                    return `${prev}${delimiter}page=${page + 1}&limit=${limit}`;
                }
            }
        },
        language: {
            'search': {
                'placeholder': '🔍 Cari No. WO / Pelanggan...'
            },
            'pagination': {
                'previous': 'Sebelumnya',
                'next': 'Selanjutnya',
                'showing': 'Menampilkan',
                'results': () => 'Data',
                'of': 'dari',
                'to': 'sampai'
            },
            'noRecordsFound': 'Tidak ada Work Order (WO) yang siap dibayar saat ini.'
        }
    }).render(document.getElementById("gridjs-table"));
});
</script>
@endsection