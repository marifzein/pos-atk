@extends('layouts.app')

@section('title', 'Buat Stock Adjustment')

@section('content')

{{-- <x-page-header
    title="Buat Stock Adjustment"
    subtitle="Input penyesuaian barang hilang, rusak, atau expired"
> --}}
<x-page-header
    :title="'Buat Stock Adjustment' . (isset($branch) ? ' - ' . $branch->name : '')"
    subtitle="Input penyesuaian barang hilang, rusak, atau expired"
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

<form
    method="POST"
    action="{{ route('stock-adjustments.store') }}"
>
@csrf

    {{-- Input Hidden branch_id --}}
    <input type="hidden" name="branch_id" value="{{ request('branch_id', $branch->id ?? 1) }}">

    <div class="grid lg:grid-cols-12 gap-6 items-start">
        
        {{-- 1. CARD KIRI: Lebih Sempit (Ukuran 3/12) --}}
        <div class="lg:col-span-3">
            <x-card>
                <div class="font-bold text-slate-800 text-sm mb-4 pb-2 border-b">
                    Informasi Dokumen SA
                </div>

                <div class="space-y-4">
                    <x-input
                        label="Nomor SA"
                        name="nomor_sa"
                        readonly
                        :value="$nomor_sa"
                        icon="ri-file-list-3-line"
                        class="bg-slate-50 font-bold text-emerald-600 text-xs"
                    />

                    <x-input
                        label="Tanggal Penyesuaian"
                        name="tgl_sa"
                        type="date"
                        :value="date('Y-m-d')"
                        required
                    />

                    <x-textarea
                        label="Catatan Umum Dokumen"
                        name="catatan"
                        rows="4"
                        placeholder="Contoh: Pembuangan barang rusak rak depan..."
                    />
                </div>
            </x-card>
        </div>

        {{-- 2. CARD KANAN: Lebih Luas (Ukuran 9/12) --}}
        <div class="lg:col-span-9 space-y-6">
            <x-card>
                {{-- Input Pencarian Produk --}}
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

                    {{-- Hasil Pencarian Floating Dropdown (Tanpa class hidden agar langsung tampil) --}}
                    <div id="product-result" class="absolute z-20 left-0 right-0 mt-1 rounded-xl border bg-white shadow-xl max-h-60 overflow-y-auto hidden">
                        <div class="p-6 text-center text-slate-400 text-sm">Ketik nama / barcode produk</div>
                    </div>
                </div>

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
                    <tbody id="sa-items">
                        <tr>
                            <td colspan="4">
                                <x-empty-state
                                    icon="ri-delete-bin-5-line"
                                    title="Belum ada produk"
                                    description="Cari produk atau scan barcode di atas."
                                />
                            </td>
                        </tr>
                    </tbody>
                </x-table>

                {{-- Tombol Aksi di Bawah Tabel --}}
                <div class="pt-6 mt-6 border-t flex justify-end items-center gap-3">
                    <a href="{{ route('stock-adjustments.index') }}">
                        <x-button color="secondary" type="button">
                            <i class="ri-close-circle-line text-red-500 text-base mr-1"></i>
                            Batal
                        </x-button>
                    </a>

                    <x-button
                        color="orange"
                        type="submit"
                        name="action"
                        value="draft"
                    >
                        <i class="ri-save-line text-base mr-1"></i>
                        F7 Simpan Draft SA
                    </x-button>

                    <x-button
                        color="green"
                        type="submit"
                        name="action"
                        value="closed"
                    >
                        <i class="ri-checkbox-circle-line text-base mr-1"></i>
                        F10 Posting & Kunci Stok
                    </x-button>
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

let cart = [];
let timer;
let selectedIndex = -1;

renderTable();
document.addEventListener('DOMContentLoaded', () => search.focus());

function clearSearch() { 
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
            result.classList.remove('hidden'); // Tampilkan dropdown pencarian

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

// 2. Keyboard Navigasi di Dropdown
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

// 3. Render Tabel Item Cart
function renderTable() {
    if (cart.length === 0) {
        saItems.innerHTML = `
        <tr>
            <td colspan="4">
                <div class="text-center py-10 text-slate-400">
                    Belum ada produk
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
                    class="qty border rounded-lg w-20 py-1 text-center font-bold text-slate-700">
                <input type="hidden" 
                    name="qty[]" 
                    value="${item.qty}" 
                    id="qty-hidden-${index}">
            </td>
            <td class="p-3">
                <input type="text" 
                    value="${item.notes}" 
                    data-index="${index}" 
                    placeholder="Alasan (contoh: Expired / Rusak)"
                    class="notes border rounded-lg w-full px-3 py-1 text-sm text-slate-700">
                <input type="hidden" 
                    name="notes[]" 
                    value="${item.notes}" 
                    id="notes-hidden-${index}">
            </td>
            <td class="p-3 text-center">
                <button type="button" class="delete text-red-600 hover:text-red-800 transition" data-index="${index}">
                    <i class="ri-delete-bin-line text-lg"></i>
                </button>
            </td>
        </tr>`;
    });

    saItems.innerHTML = html;
}

// Event input qty & notes
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
    const btn = e.target.closest('.delete');
    if (!btn) return;
    cart.splice(btn.dataset.index, 1);
    renderTable();
    search.focus();
});

// Tutup dropdown jika klik di luar
document.addEventListener('click', (e) => { 
    if (!search.contains(e.target) && !result.contains(e.target)) {
        result.classList.add('hidden');
    } 
});

// 4. Keyboard Shortcut F2, F7, F10
document.addEventListener('keydown', function(e) {
    const form = document.querySelector('form[action="{{ route("stock-adjustments.store") }}"]');
    if (!form) return;

    if (e.key === 'F2') {
        e.preventDefault();
        search.focus();
        search.select();
    }

    if (e.key === 'F7') {
        e.preventDefault();
        if (cart.length === 0) { 
            Swal.fire({ title: 'Peringatan', text: 'Daftar item adjustment masih kosong!', icon: 'warning', confirmButtonColor: '#f97316' }); 
            return; 
        }

        const emptyNotes = cart.some(item => !item.notes || item.notes.trim() === '');
        if (emptyNotes) {
            Swal.fire({ title: 'Peringatan', text: 'Semua item wajib diisi alasan penyesuaiannya!', icon: 'warning', confirmButtonColor: '#f97316' });
            return;
        }
        
        Swal.fire({
            title: 'Simpan sebagai Draft?',
            text: 'Data SA akan disimpan dengan status Draft.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f97316',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Simpan Draft!',
            cancelButtonText: 'Batal'
        }).then((res) => {
            if (res.isConfirmed) {
                const oldAction = form.querySelector('input[name="action"]');
                if (oldAction) oldAction.remove();

                const inputDraft = document.createElement('input');
                inputDraft.type = 'hidden';
                inputDraft.name = 'action';
                inputDraft.value = 'draft';
                form.appendChild(inputDraft);

                form.submit();
            }
        });
    }

    if (e.key === 'F10') {
        e.preventDefault();
        if (cart.length === 0) { 
            Swal.fire({ title: 'Peringatan', text: 'Daftar item adjustment masih kosong!', icon: 'warning', confirmButtonColor: '#22c55e' }); 
            return; 
        }

        const emptyNotes = cart.some(item => !item.notes || item.notes.trim() === '');
        if (emptyNotes) {
            Swal.fire({ title: 'Peringatan', text: 'Semua item wajib diisi alasan penyesuaiannya!', icon: 'warning', confirmButtonColor: '#22c55e' });
            return;
        }

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
});
</script>
@endpush

@endsection