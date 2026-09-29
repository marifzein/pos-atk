@extends('layouts.app')

@section('title', 'Kasir - Daftar Pesanan')

@section('content')

{{-- gridjs bos --}}
{{-- <link href="https://unpkg.com/gridjs/dist/theme/mermaid.min.css" rel="stylesheet" />
<script src="https://unpkg.com/gridjs/dist/gridjs.umd.js"></script> --}}

<link href="{{ asset('css/gridjs/mermaid.min.css') }}" rel="stylesheet" />
<script src="{{ asset('js/gridjs/gridjs.umd.js') }}"></script>





{{-- <x-page-header title="POS / Kasir" subtitle="Daftar pesanan yang siap diproses menjadi Nota">
    
</x-page-header> --}}

<!-- Header Halaman + Pengingat Badge Interaktif -->
{{-- <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-6"> --}}
<!-- Header Halaman + Pengingat Bersusun Presisi Sesuai Skrinsut -->
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800">POS / Kasir</h1>
        <p class="text-sm text-slate-500 mt-0.5">Daftar pesanan yang siap diproses menjadi Nota Penjualan</p>
    </div>

    <!-- Container Pengingat Blok Kanan -->
    <div class="flex flex-col items-start gap-1 text-xs">
        <!-- Judul Ingat! Sejajar dengan Tombol Badge -->
        <span class="text-red-600 font-bold text-sm tracking-wide"><i class="ri-file-warning-line font-normal"></i> REMINDER</span>

        <div class="space-y-1.5 mt-0.5">
            <!-- Baris Tutup Shift -->
            <div class="flex items-center gap-2.5">
                <a href="{{ route('kasir.close-shift') }}" 
                   class="w-24 inline-flex items-center justify-center px-3 py-1 rounded-full bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-400 font-semibold text-xs shadow-2xs transition">
                    Tutup Shift
                </a>
                <span class="text-slate-600 font-medium">Saat jam kerja selesai</span>
            </div>

            <!-- Baris Logout -->
            <div class="flex items-center gap-2.5">
                <button type="button" 
                        onclick="confirmLogout()" 
                        class="w-24 inline-flex items-center justify-center px-3 py-1 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-800 border border-rose-300 font-semibold text-xs shadow-2xs transition cursor-pointer">
                    Logout
                </button>
                <span class="text-slate-600 font-medium">Saat meninggalkan komputer</span>
            </div>
        </div>
    </div>
</div>


<!-- Form Logout Tersembunyi (Laravel CSRF) -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
    @csrf
</form>


<!-- Container Card Utama -->
<div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-5">
    <div id="gridjs-table"></div>
</div>

<script>
    // Function SweetAlert Konfirmasi Logout
    function confirmLogout() {
        Swal.fire({
            title: 'Log Out sekarang?',
            text: 'Pastikan pekerjaan atau transaksi Anda sudah selesai.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48', // Red-600
            cancelButtonColor: '#64748b',  // Slate-500
            confirmButtonText: 'Ya, Log Out!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-4 py-2 font-semibold',
                cancelButton: 'rounded-xl px-4 py-2 font-semibold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        new gridjs.Grid({
            columns: [
                // 1. KOLOM NO WO / SP
                {
                    name: 'No WO / SP',
                    formatter: (cell) => {
                        const noPesanan = cell.no_pesanan || '-';
                        const branchName = cell.branch_name || '-';
                        const sourceBadge = cell.order_source === 'kasir' 
                            ? '<span class="px-1.5 py-0.5 text-[10px] bg-indigo-100 text-indigo-700 font-bold rounded">SP KASIR</span>' 
                            : '<span class="px-1.5 py-0.5 text-[10px] bg-slate-100 text-slate-700 font-bold rounded">WO STAF</span>';

                        return gridjs.html(`
                            <div class="space-y-0.5">
                                <span class="font-bold text-slate-900 font-mono text-sm block">${noPesanan}</span>
                                <div class="flex items-center gap-1.5 text-xs text-slate-500">
                                    ${sourceBadge}
                                    <span>${branchName}</span>
                                </div>
                            </div>
                        `);
                    }
                },

                // 2. KOLOM TANGGAL
                {
                    name: 'Tgl',
                    formatter: (cell) => {
                        return gridjs.html(`
                            <div class="text-xs text-slate-600">
                                <div class="font-medium text-slate-800">${cell.tanggal}</div>
                                <div class="text-[11px] text-slate-400 font-mono">${cell.jam}</div>
                            </div>
                        `);
                    }
                },

                // 3. KOLOM ITEM
                { name: 'Item' },

                // 4. KOLOM OPERATOR
                { name: 'Operator' },

                // 5. KOLOM PELANGGAN
                { name: 'Pelanggan' },

                // 6. KOLOM STATUS
                {
                    name: 'Status',
                    formatter: (cell) => {
                        let statusOrder = `<span class="px-2 py-0.5 text-xs font-bold rounded bg-slate-100 text-slate-700">${cell.status}</span>`;
                        let badgeBayar = '';

                        const pStatus = (cell.payment_status || '').toLowerCase();

                        if (pStatus === 'lunas' || cell.status === 'LUNAS') {
                            badgeBayar = ''; // Tidak perlu badge tambahan karena status utamanya sudah LUNAS
                        } else if (pStatus === 'dp' || Number(cell.total_dp) > 0) {
                            badgeBayar = `<span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-amber-50 text-amber-700 border border-amber-200 block mt-1">DP Masuk</span>`;
                        } else {
                            badgeBayar = `<span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-rose-50 text-rose-700 border border-rose-200 block mt-1">Belum Bayar</span>`;
                        }

                        return gridjs.html(`
                            <div class="text-center">
                                ${statusOrder}
                                ${badgeBayar}
                            </div>
                        `);
                    }
                },

                // 7. KOLOM TOTAL & SISA
                {
                    name: 'Total & Sisa',
                    formatter: (cell) => {
                        return gridjs.html(`
                            <div class="text-right">
                                <div class="font-bold text-slate-900">${cell.total}</div>
                                <div class="text-xs font-semibold ${cell.sisa === 'Rp 0' ? 'text-emerald-600' : 'text-rose-600'}">
                                    Sisa: ${cell.sisa}
                                </div>
                            </div>
                        `);
                    }
                },

                // 8. KOLOM AKSI
                {
                    name: 'Aksi',
                    formatter: (cell) => {
                        // Bypass cabang jika user adalah developer, admin, atau owner
                        const isAuthorized = cell.is_same_branch || @json(in_array(strtolower(Auth::user()->role), 
                        ['admin', 'owner']));

                        if (!isAuthorized) {
                            return gridjs.html(`
                                <span class="px-2.5 py-1 text-xs bg-slate-100 text-slate-400 rounded-lg font-medium inline-block">
                                    Beda Cabang
                                </span>
                            `);
                        }

                        // Jika pesanan sudah lunas di awal (sisa = Rp 0), tampilkan tombol Cetak NP / Proses NP
                        if (cell.is_lunas) {
                            return gridjs.html(`
                                <a href="${cell.url}" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold inline-flex items-center gap-1.5 shadow-xs transition">
                                    <i class="ri-printer-line"></i> Cetak NP
                                </a>
                            `);
                        }

                        // Jika SP kasir dengan sisa pembayaran
                        if (cell.is_kasir_order) {
                            return gridjs.html(`
                                <a href="${cell.url}" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold inline-flex items-center gap-1.5 shadow-xs transition">
                                    <i class="ri-money-dollar-circle-line"></i> Pelunasan
                                </a>
                            `);
                        }

                        // Pesanan reguler belum lunas
                        return gridjs.html(`
                            <a href="${cell.url}" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold inline-flex items-center gap-1.5 shadow-xs transition">
                                <i class="ri-wallet-3-line"></i> Bayar
                            </a>
                        `);
                    }
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
                    'placeholder': '🔍 Cari No. WO / SP / Pelanggan...'
                },
                'pagination': {
                    'previous': '<',
                    'next': '>',
                    'showing': 'Menampilkan',
                    'results': () => 'Data',
                    'of': 'dari',
                    'to': 'sampai'
                },
                'noRecordsFound': 'Tidak ada pesanan yang siap dibayar saat ini.'
            }
        }).render(document.getElementById("gridjs-table"));
    });
</script>
@endsection