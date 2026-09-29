@extends('layouts.app')

@php
    $branchName =$stockAdjustment->branch->name ?? '';
    $pageTitle = ($stockAdjustment->status === 'closed' ? 'Detail Stock Adjustment' : 'Edit Stock Adjustment') . ($branchName ? ' - ' . $branchName : '');
    $isClosed =$stockAdjustment->status === 'closed';
@endphp

@section('title', $pageTitle)

@section('content')

<x-page-header
    :title="$pageTitle"
    subtitle="{{ $isClosed ? 'Melihat dokumen penyesuaian yang telah dikunci' : 'Ubah draft atau teruskan posting penyesuaian stok' }}"
>
    <x-slot:action>
        <a href="{{ route('stock-adjustments.index') }}">
            <x-button color="gray" type="button">
                <i class="ri-arrow-left-line"></i>
                Kembali
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

@if ($errors->any())
    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-xl">
        <ul class="list-disc pl-5 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" id="form-sa" action="{{ route('stock-adjustments.update', $stockAdjustment->id) }}">
    @csrf
    @method('PUT')

    <div class="grid lg:grid-cols-12 gap-6 items-start">
        
        {{-- CARD KIRI: Informasi Dokumen (Ukuran 3/12) --}}
        <div class="lg:col-span-3">
            <x-card>
                <div class="font-bold text-slate-800 text-sm mb-4 pb-2 border-b flex justify-between items-center">
                    <span>Informasi Dokumen SA</span>
                    @if($isClosed)
                        <span class="px-2 py-0.5 text-xs bg-emerald-100 text-emerald-700 font-semibold rounded-full">Closed</span>
                    @else
                        <span class="px-2 py-0.5 text-xs bg-amber-100 text-amber-700 font-semibold rounded-full">Draft</span>
                    @endif
                </div>

                <div class="space-y-4">
                    <x-input
                        label="Nomor SA"
                        name="nomor_sa"
                        readonly
                        :value="$stockAdjustment->nomor_sa"
                        icon="ri-file-list-3-line"
                        class="bg-slate-50 font-bold text-emerald-600 text-xs"
                    />

                    <x-input
                        label="Tanggal Penyesuaian"
                        name="tgl_sa"
                        type="date"
                        :value="date('Y-m-d', strtotime($stockAdjustment->tgl_sa))"
                        required
                        :readonly="$isClosed"
                    />

                    <x-textarea
                        label="Catatan Umum Dokumen"
                        name="catatan"
                        rows="4"
                        :value="$stockAdjustment->catatan"
                        placeholder="Contoh: Pembuangan barang rusak rak depan..."
                        :readonly="$isClosed"
                    />
                </div>
            </x-card>
        </div>

        {{-- CARD KANAN: Input Produk, Tabel, & Tombol Aksi (Ukuran 9/12) --}}
        <div class="lg:col-span-9 space-y-6">
            <x-card>
                {{-- Input Pencarian Produk (Hanya tampil jika bukan closed) --}}
                @if(!$isClosed)
                    <div class="relative mb-5">
                        <div class="font-semibold mb-2 flex items-center justify-between text-sm text-slate-700">
                            <label for="search-product">F2 - Cari Produk / Scan Barcode</label>
                            <span class="text-xs px-2 py-0.5 bg-slate-900 text-emerald-400 font-mono rounded-md">
                                <i class="ri-scan-2-line"></i> Scanner Ready
                            </span>
                        </div>

                        <x-input
                            id="search-product"
                            name="search"
                            placeholder="Scan Barcode / Ketik Kode atau Nama Barang..."
                            icon="ri-search-line"
                            autocomplete="off"
                            class="text-base"
                        />

                        {{-- Floating Dropdown Pencarian --}}
                        <div id="product-result" class="absolute z-20 left-0 right-0 mt-1 rounded-xl border bg-white shadow-xl max-h-60 overflow-y-auto hidden">
                            <div class="p-6 text-center text-slate-400 text-sm">Ketik nama / barcode produk</div>
                        </div>
                    </div>
                @endif

                {{-- Header Keranjang --}}
                <div class="mb-3">
                    <h3 class="font-bold text-slate-800 text-sm">Daftar Item Dibuang / Dikurangi</h3>
                    <p class="text-xs text-slate-500">Kuantitas di bawah ini akan langsung memotong stok di komputer</p>
                </div>

                {{-- Tabel Cart Item --}}
                <x-table>
                    <x-table-header>
                        <tr>
                            <x-table-head class="text-left">Produk</x-table-head>
                            <x-table-head class="text-center w-28">Qty</x-table-head>
                            <x-table-head class="text-left">Alasan / Keterangan</x-table-head>
                            <x-table-head class="text-center w-16">Aksi</x-table-head>
                        </tr>
                    </x-table-header>
                    <tbody id="sa-items"></tbody>
                </x-table>

                {{-- Tombol Aksi di Bawah Tabel --}}
                <div class="pt-6 mt-6 border-t flex justify-end items-center gap-3">
                    <a href="{{ route('stock-adjustments.index') }}">
                        <x-button color="secondary" type="button">
                            <i class="ri-close-circle-line text-red-500 text-base mr-1"></i>
                            Kembali
                        </x-button>
                    </a>

                    @if(!$isClosed)
                        <x-button
                            color="orange"
                            type="button"
                            onclick="submitAction('draft')"
                        >
                            <i class="ri-save-line text-base mr-1"></i>
                            F7 Perbarui Draft SA
                        </x-button>

                        <x-button
                            color="green"
                            type="button"
                            onclick="submitAction('closed')"
                        >
                            <i class="ri-checkbox-circle-line text-base mr-1"></i>
                            F10 Posting & Kunci Stok
                        </x-button>
                    @endif
                </div>
            </x-card>
        </div>

    </div>
</form>

@push('scripts')
<script>
const search = document.getElementById('search-product');
const result = document.getElementById('product-result');
const saItems = document.getElementById('sa-items');
const form = document.getElementById('form-sa');

const isDocumentClosed = {{ $isClosed ? 'true' : 'false' }};
let cart = @json($cartData); 

let timer;
let selectedIndex = -1;

renderTable();

if (!isDocumentClosed && search) {
    document.addEventListener('DOMContentLoaded', () => search.focus());
}

function clearSearch() { 
    if (!result || !search) return;
    result.innerHTML = `<div class="p-6 text-center text-slate-400 text-sm">Ketik nama / barcode produk</div>`; 
    result.classList.add('hidden');
    search.value = ''; 
    selectedIndex = -1; 
    search.focus(); 
}

function addToCart(id, name, code) {
    const existing = cart.find(x => x.id == id);
    if (existing) { 
        existing.qty++; 
    } else { 
        cart.push({ id: id, name: name, code: code, qty: 1, notes: '' }); 
    }
    renderTable(); 
    clearSearch();
}

// 1. Pencarian Real-Time
if (!isDocumentClosed && search) {
    search.addEventListener('keyup', function(e) {
        if (['ArrowUp', 'ArrowDown', 'Enter'].includes(e.key)) return;
        clearTimeout(timer);
        const q = this.value.trim();
        if (q.length < 2) { 
            result.classList.add('hidden');
            return; 
        }

        timer = setTimeout(() => {
            fetch("{{ url('/api/products/search') }}?q=" + encodeURIComponent(q))
            .then(r => r.json())
            .then(data => {
                result.classList.remove('hidden');

                if (data.length === 0) {
                    result.innerHTML = `<div class="p-4 text-center text-slate-400 text-sm">Produk tidak ditemukan</div>`;
                    return;
                }

                const firstItemName = data[0].name || data[0].nama_barang;
                const firstItemCode = data[0].sku || data[0].kode_barang || data[0].barcode;
                if (data.length === 1 && (firstItemCode?.toLowerCase() === q.toLowerCase())) {
                    addToCart(data[0].id, firstItemName, firstItemCode);
                    return;
                }

                let html = '';
                data.forEach((item, index) => {
                    const prodName = item.name || item.nama_barang || '-';
                    const prodCode = item.sku || item.kode_barang || item.barcode || '-';
                    
                    html += `
                    <div class="border-b p-3 hover:bg-emerald-50 cursor-pointer product-row" 
                        id="product-row-${index}" 
                        data-id="${item.id}" 
                        data-name="${prodName}" 
                        data-code="${prodCode}">
                        <div class="font-semibold text-slate-800 text-sm">${prodName}</div>
                        <div class="text-xs text-slate-500">${prodCode}</div>
                    </div>`;
                });
                result.innerHTML = html; 
                selectedIndex = -1;

                document.querySelectorAll('.product-row').forEach(row => {
                    row.onclick = function() { 
                        addToCart(this.dataset.id, this.dataset.name, this.dataset.code); 
                    }
                });
            });
        }, 250);
    });

    // 2. Navigasi Keyboard Dropdown
    search.addEventListener('keydown', function(e) {
        const rows = document.querySelectorAll('.product-row'); 

        if (e.key === 'Enter') {
            e.preventDefault(); 
            if (selectedIndex >= 0 && rows.length > 0 && selectedIndex < rows.length) { 
                const r = rows[selectedIndex]; 
                addToCart(r.dataset.id, r.dataset.name, r.dataset.code); 
            } 
            else if (rows.length === 1) {
                const r = rows[0];
                addToCart(r.dataset.id, r.dataset.name, r.dataset.code);
            }
            return;
        }
        
        if (rows.length === 0) return;
        
        if (e.key === 'ArrowDown') { 
            e.preventDefault(); 
            selectedIndex++; 
            if (selectedIndex >= rows.length) selectedIndex = 0; 
            updateRowHighlight(rows); 
        } 
        else if (e.key === 'ArrowUp') { 
            e.preventDefault(); 
            selectedIndex--; 
            if (selectedIndex < 0) selectedIndex = rows.length - 1; 
            updateRowHighlight(rows); 
        } 
    });
}

function updateRowHighlight(rows) {
    rows.forEach((row, i) => { 
        if (i === selectedIndex) { 
            row.classList.add('bg-emerald-100'); 
            row.scrollIntoView({ block: 'nearest' }); 
        } else { 
            row.classList.remove('bg-emerald-100'); 
        } 
    });
}

// 3. Render Tabel Cart
function renderTable() {
    if (cart.length === 0) {
        saItems.innerHTML = `
        <tr>
            <td colspan="4">
                <div class="text-center py-10 text-slate-400">
                    Belum ada produk dipilih
                </div>
            </td>
        </tr>`;
        return;
    }

    let html = '';
    cart.forEach((item, index) => {
        html += `
        <tr class="border-b">
            <td class="p-3">
                <div class="font-semibold text-slate-800 text-sm">${item.name}</div>
                <div class="text-xs text-slate-400">${item.code}</div>
                <input type="hidden" name="product_id[]" value="${item.id}">
            </td>
            <td class="p-3 text-center">
                <input type="number" 
                    min="1" 
                    value="${item.qty}" 
                    data-index="${index}" 
                    ${isDocumentClosed ? 'disabled' : ''}
                    class="qty border rounded-lg w-20 py-1 text-center font-bold text-slate-700 ${isDocumentClosed ? 'bg-slate-100' : ''}">
                <input type="hidden" 
                    name="qty[]" 
                    value="${item.qty}" 
                    id="qty-hidden-${index}">
            </td>
            <td class="p-3">
                <input type="text" 
                    value="${item.notes || ''}" 
                    data-index="${index}" 
                    placeholder="Alasan (contoh: Expired / Rusak)"
                    ${isDocumentClosed ? 'disabled' : ''}
                    class="notes border rounded-lg w-full px-3 py-1 text-sm text-slate-700 ${isDocumentClosed ? 'bg-slate-100' : ''}">
                <input type="hidden" 
                    name="notes[]" 
                    value="${item.notes || ''}" 
                    id="notes-hidden-${index}">
            </td>
            <td class="p-3 text-center">
                ${isDocumentClosed ? '<span class="text-slate-400">-</span>' : `
                <button type="button" class="delete text-red-600 hover:text-red-800 transition" data-index="${index}">
                    <i class="ri-delete-bin-line text-lg"></i>
                </button>`}
            </td>
        </tr>`;
    });

    saItems.innerHTML = html;
}

// Input real-time
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('qty')) {
        const index = e.target.dataset.index;
        let val = parseInt(e.target.value);
        if (isNaN(val) || val < 1) val = 1;
        cart[index].qty = val;
        document.getElementById(`qty-hidden-${index}`).value = val;
    }

    if (e.target.classList.contains('notes')) {
        const index = e.target.dataset.index;
        cart[index].notes = e.target.value;
        document.getElementById(`notes-hidden-${index}`).value = e.target.value;
    }
});

// Hapus item
document.addEventListener('click', function(e) {
    if (isDocumentClosed) return;
    const btn = e.target.closest('.delete');
    if (!btn) return;
    cart.splice(btn.dataset.index, 1);
    renderTable();
    if (search) search.focus();
});

// Tutup dropdown di luar klik
document.addEventListener('click', (e) => { 
    if (search && result && !search.contains(e.target) && !result.contains(e.target)) {
        result.classList.add('hidden');
    } 
});

// Validasi Form
function validateCart() {
    if (cart.length === 0) { 
        Swal.fire({ title: 'Peringatan', text: 'Daftar item adjustment masih kosong!', icon: 'warning', confirmButtonColor: '#f97316' }); 
        return false; 
    }

    const emptyNotes = cart.some(item => !item.notes || item.notes.trim() === '');
    if (emptyNotes) {
        Swal.fire({ title: 'Peringatan', text: 'Semua item wajib diisi alasan penyesuaiannya!', icon: 'warning', confirmButtonColor: '#f97316' });
        return false;
    }
    return true;
}

// Eksekusi Submit Form (Draft / Closed)
function submitAction(actionValue) {
    if (isDocumentClosed || !validateCart()) return;

    if (actionValue === 'draft') {
        Swal.fire({
            title: 'Perbarui Draft SA?',
            text: 'Data SA akan disimpan dengan status Draft dan bisa diubah kembali.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f97316',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Perbarui!',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                let actInput = form.querySelector('input[name="action"]');
                if (!actInput) {
                    actInput = document.createElement('input');
                    actInput.type = 'hidden';
                    actInput.name = 'action';
                    form.appendChild(actInput);
                }
                actInput.value = 'draft';
                form.submit();
            }
        });
    }

    if (actionValue === 'closed') {
        Swal.fire({
            title: 'Posting & Kunci Stok?',
            text: 'Status SA akan diubah menjadi CLOSED dan langsung memotong stok cabang.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#22c55e',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Posting!',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Data...',
                    text: 'Mohon tunggu sebentar...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const oldAction = form.querySelector('input[name="action"]');
                if (oldAction) oldAction.remove();

                const formData = new FormData(form);
                formData.append('action', 'closed'); 

                fetch(form.getAttribute('action'), {
                    method: 'POST', 
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json' 
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#22c55e'
                        }).then(() => {
                            window.location.href = "{{ route('stock-adjustments.index') }}";
                        });
                    } else {
                        Swal.fire({ title: 'Gagal', text: data.message, icon: 'error', confirmButtonColor: '#ef4444' });
                    }
                })
                .catch(err => {
                    Swal.fire({ title: 'Error!', text: 'Terjadi kesalahan sistem.', icon: 'error', confirmButtonColor: '#ef4444' });
                });
            }
        });
    }
}

// 4. Keyboard Shortcuts F2, F7, F10
document.addEventListener('keydown', function(e) {
    if (isDocumentClosed || !form) return;

    if (e.key === 'F2') {
        e.preventDefault();
        if (search) {
            search.focus();
            search.select();
        }
    }

    if (e.key === 'F7') {
        e.preventDefault();
        submitAction('draft');
    }

    if (e.key === 'F10') {
        e.preventDefault();
        submitAction('closed');
    }
});
</script>
@endpush

@endsection