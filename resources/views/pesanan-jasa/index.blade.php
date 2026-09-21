@extends('layouts.app')

@section('title', 'Pesanan Jasa')

@section('content')
<!-- Judul Halaman -->
<div class="mb-3 md:mb-4 px-1">
    <h2 class="text-lg md:text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
        <i class="ri-customer-service-2-line text-indigo-600 text-xl"></i> 
        <span>Pesanan <span class="text-xs md:text-sm font-normal text-slate-500">( Layanan Jasa )</span></span>
    </h2>
</div>

<!-- CONTAINER UTAMA POS JASA -->
<div class="w-full px-0 md:px-1" x-data="posJasa()">
    <!-- Grid System Responsif: 1 Kolom di HP (grid-cols-1), 12 Kolom di Desktop (lg:grid-cols-12) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 md:gap-5 items-start w-full">

        <!-- ==================== CARD KIRI / ATAS (Form Pelanggan & Info) ==================== -->
        <div class="col-span-1 lg:col-span-4 xl:col-span-3 space-y-3 md:space-y-4 w-full">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-3.5 md:p-5 space-y-4">
                
                <!-- 1. No Pesanan -->
                <div>
                    <label class="block text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-1">No. Pesanan Jasa</label>
                    <input type="text" value="{{ $nomorWO }}" readonly class="w-full border border-slate-200 rounded-lg p-2.5 bg-slate-50 text-slate-700 font-bold text-sm outline-none shadow-inner">
                </div>

                <!-- 2. Pelanggan -->
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <label class="block text-[11px] text-slate-400 font-bold uppercase tracking-wider">Pelanggan (F8)</label>
                        <button type="button" @click="$dispatch('open-customer-modal')" class="text-[11px] text-indigo-600 font-bold hover:underline cursor-pointer flex items-center gap-0.5">
                            <i class="ri-user-add-line"></i> + Tambah
                        </button>
                    </div>
                    <div class="relative" @click.outside="customerResults=[]">
                        <input x-ref="customerInput" type="text" x-model="customerSearch" @keydown.arrow-down.prevent="moveCustomerDown()" @keydown.arrow-up.prevent="moveCustomerUp()" @keydown.enter.prevent="chooseCustomer()" @input="searchCustomer()" placeholder="Cari nama / kode pelanggan..." class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-sm shadow-sm">
                        
                        <!-- Dropdown Pelanggan -->
                        <div x-show="customerResults.length" class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-xl z-50 max-h-60 overflow-y-auto">
                            <template x-for="(customer, index) in customerResults" :key="customer.kode_pelanggan">
                                <div @click="selectCustomer(customer)" :class="customerIndex === index ? 'bg-indigo-50 text-indigo-900' : ''" class="px-4 py-2.5 cursor-pointer hover:bg-slate-50 border-b border-slate-100 text-sm">
                                    <div class="font-semibold text-slate-800" x-text="customer.nama"></div>
                                    <div class="text-xs text-slate-400" x-text="customer.kode_pelanggan"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Detail Pelanggan Terpilih -->
                    <template x-if="selectedCustomer">
                        <div class="mt-2 rounded-lg bg-indigo-50/60 border border-indigo-100 p-2.5 relative text-xs flex justify-between items-center">
                            <div>
                                <div class="font-bold text-indigo-900" x-text="selectedCustomer.nama"></div>
                                <div class="text-slate-500 text-[11px]" x-text="'Telp: ' + (selectedCustomer.telepon || '-')"></div>
                            </div>
                            <button @click="clearCustomer()" class="text-rose-600 font-semibold hover:bg-rose-50 px-2 py-1 rounded transition">Hapus</button>
                        </div>
                    </template>
                </div>

                <!-- 3. Info Shortcut Keyboard (Disembunyikan di HP, Muncul di Desktop) -->
                <div class="hidden lg:block bg-amber-50/50 border border-amber-200 rounded-xl p-3.5">
                    <label class="block text-[11px] text-amber-800 font-bold uppercase tracking-wider mb-2 flex items-center gap-1">
                        <i class="ri-keyboard-line"></i> Shortcut Keyboard
                    </label>
                    <div class="space-y-1.5 text-xs text-amber-900/90">
                        <div class="flex justify-between"><span class="text-slate-500">Cari Jasa</span><span class="font-mono bg-white px-1.5 py-0.5 border rounded shadow-sm text-[10px]">F2</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Pilih Item</span><span class="font-mono bg-white px-1.5 py-0.5 border rounded shadow-sm text-[10px]">↑ / ↓</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Masuk Chart</span><span class="font-mono bg-white px-1.5 py-0.5 border rounded shadow-sm text-[10px]">Enter</span></div>
                        <div class="flex justify-between"><span>F3</span><span class="font-medium text-slate-800">Cek Harga Barang</span></div>
                        <div class="flex justify-between"><span>F8</span><span class="font-medium text-slate-800">Pelanggan</span></div>
                        <div class="flex justify-between"><span>F10</span><span class="font-medium text-slate-800">Simpan</span></div>
                        <div class="flex justify-between text-rose-600"><span>Ctrl+Del</span><span class="font-semibold">Kosongkan Cart</span></div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ==================== CARD KANAN / BAWAH (Input Jasa & Keranjang) ==================== -->
        <div class="col-span-1 lg:col-span-8 xl:col-span-9 w-full">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-3.5 md:p-5 flex flex-col min-h-[480px] md:min-h-[560px] w-full">
                
                <!-- Input Pencarian Jasa -->
                <div class="mb-4">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="ri-search-2-line text-lg"></i>
                        </span>
                        <input id="jasaInput" x-ref="jasaInput" type="text" x-model="search" @input="searchJasa" @keydown.arrow-down.prevent="if(selectedIndex < jasaItems.length - 1) selectedIndex++" @keydown.arrow-up.prevent="if(selectedIndex > 0) selectedIndex--" @keydown.enter.prevent="if(jasaItems.length) addToCart(jasaItems[selectedIndex])" placeholder="Scan Barcode / Kode / Nama Jasa..." class="w-full border border-indigo-600 rounded-xl py-3 pl-10 pr-10 text-sm md:text-base focus:ring-2 focus:ring-indigo-100 outline-none shadow-sm font-medium placeholder-slate-400">
                        
                        <!-- Tombol Buka Modal Cek Harga (Mobile Friendly) -->
                        <button type="button" @click="showPriceModal = true; setTimeout(() => { $refs.priceInput?.focus(); }, 50);" class="absolute inset-y-1.5 right-1.5 bg-indigo-50 text-indigo-600 px-2.5 rounded-lg text-xs font-bold flex items-center gap-1 hover:bg-indigo-100 transition" title="Cek Harga (F3)">
                            <i class="ri-price-tag-3-line"></i> <span class="hidden sm:inline">Cek Harga</span>
                        </button>

                        <!-- Dropdown List Hasil Pencarian Jasa -->
                        <div x-show="jasaItems.length" class="absolute left-0 right-0 bg-white border border-slate-200 rounded-xl shadow-2xl mt-1.5 z-50 max-h-64 overflow-y-auto">
                            <template x-for="(jasa, index) in jasaItems" :key="jasa.id">
                                <div @click="addToCart(jasa)" :class="selectedIndex === index ? 'bg-indigo-600 text-white' : 'hover:bg-slate-50'" class="p-3 border-b border-slate-100 cursor-pointer flex justify-between items-center transition-colors">
                                    <div>
                                        <div class="font-bold text-sm" :class="selectedIndex === index ? 'text-white' : 'text-slate-800'" x-text="jasa.name"></div>
                                        <div class="text-xs" :class="selectedIndex === index ? 'text-indigo-200' : 'text-slate-400'" x-text="'Kode: ' + (jasa.barcode || jasa.id)"></div>
                                    </div>
                                    <div class="font-extrabold text-sm" :class="selectedIndex === index ? 'text-white' : 'text-indigo-600'" x-text="formatRupiah(jasa.price)"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Label & Header Keranjang Belanja -->
                <div class="flex justify-between items-center mb-2 px-1">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                        <i class="ri-shopping-cart-2-line text-indigo-600"></i> Keranjang Belanja (<span x-text="cart.length"></span>)
                    </div>
                    <button x-show="cart.length > 0" @click="clearCart()" class="text-xs text-rose-600 font-semibold hover:underline">
                        Kosongkan Cart
                    </button>
                </div>

                <!-- 1. TAMPILAN MOBILE (LIST / CARDS) - Terlihat di Layar HP (< 768px) -->
                <div class="block md:hidden space-y-2.5 mb-auto">
                    <template x-for="(item, index) in cart" :key="item.id">
                        <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-3 space-y-2.5 relative">
                            <!-- Judul Item & Hapus -->
                            <div class="flex justify-between items-start gap-2 pr-6">
                                <div class="font-bold text-slate-800 text-sm">
                                    <span x-text="item.nama_barang"></span>
                                    <template x-if="item.is_custom">
                                        <span class="inline-block text-[9px] bg-amber-50 text-amber-700 border border-amber-200 px-1 py-0.2 rounded font-medium ml-1">Custom</span>
                                    </template>
                                </div>
                                <button @click="removeItem(item.id)" class="absolute top-2.5 right-2 text-slate-400 hover:text-rose-600 p-1">
                                    <i class="ri-delete-bin-line text-lg"></i>
                                </button>
                            </div>

                            <!-- Input Qty, Harga & Subtotal -->
                            <div class="flex items-center justify-between pt-1 border-t border-slate-200/60 text-xs">
                                <!-- Qty -->
                                <div class="flex items-center gap-1">
                                    <span class="text-slate-500 font-medium">Qty:</span>
                                    <input type="number" min="1" x-model.number="item.qty" @change="validateQty(item)" @input="calculateItem(item)" class="w-16 border border-slate-300 bg-white rounded-md text-center py-1 font-bold text-sm focus:border-indigo-500 outline-none">
                                </div>

                                <!-- Harga (Jika Custom) / Subtotal -->
                                <div class="text-right">
                                    <template x-if="item.is_custom">
                                        <div class="flex items-center justify-end gap-1 mb-0.5">
                                            <span class="text-slate-400 text-[10px]">Rp</span>
                                            <input type="number" min="0" x-model.number="item.harga" @input="validateHarga(item)" class="w-24 border border-amber-300 bg-amber-50/50 rounded text-right px-1 py-0.5 font-bold text-xs outline-none">
                                        </div>
                                    </template>
                                    <div class="font-black text-slate-900 text-sm" x-text="formatRupiah(item.qty * item.harga)"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div x-show="cart.length === 0" class="text-center py-12 text-slate-400 italic text-xs bg-slate-50/40 rounded-xl border border-dashed border-slate-200">
                        Belum ada item dimasukkan
                    </div>
                </div>

                <!-- 2. TAMPILAN DESKTOP (TABEL) - Terlihat di Layar Tablet / PC (>= 768px) -->
                <div class="hidden md:block overflow-x-auto border border-slate-100 rounded-lg w-full mb-auto">
                    <table class="w-full border-collapse bg-white table-auto">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                <th class="p-3 text-center w-12">No</th>
                                <th class="text-left p-3">Item Jasa</th>
                                <th class="p-3 w-28 text-center">Qty</th>
                                <th class="text-right p-3 w-36">Harga</th>
                                <th class="text-right p-3 w-40">Jumlah</th>
                                <th class="w-12 text-center"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in cart" :key="item.id">
                                <tr class="text-slate-700 text-sm hover:bg-slate-50/50 transition">
                                    <td class="p-3 text-center text-slate-400 font-medium" x-text="index + 1"></td>
                                    <td class="p-3 font-semibold text-slate-800">
                                        <div class="flex items-center gap-2">
                                            <span x-text="item.nama_barang"></span>
                                            <template x-if="item.is_custom">
                                                <span class="text-[10px] bg-amber-50 text-amber-700 border border-amber-200 px-1.5 py-0.5 rounded font-medium">Custom</span>
                                            </template>
                                        </div>
                                    </td>

                                    <td class="p-3 text-center">
                                        <input type="number" min="1" x-model.number="item.qty" @change="validateQty(item)" @input="calculateItem(item)" @keydown.enter.prevent="$refs.jasaInput.focus()" class="w-20 border border-slate-300 rounded-md text-center p-1.5 font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                                    </td>

                                    <!-- HARGA ITEM: JIKA CUSTOM BISA DIEDIT -->
                                    <td class="p-3 text-right">
                                        <template x-if="item.is_custom">
                                            <div class="flex items-center justify-end gap-1">
                                                <span class="text-slate-400 text-xs font-semibold">Rp</span>
                                                <input 
                                                    type="number" 
                                                    min="0" 
                                                    x-model.number="item.harga" 
                                                    @input="validateHarga(item)" 
                                                    @keydown.enter.prevent="$refs.jasaInput.focus()" 
                                                    class="w-28 border border-amber-300 bg-amber-50/30 rounded-md text-right p-1.5 font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-800"
                                                >
                                            </div>
                                        </template>
                                        <template x-if="!item.is_custom">
                                            <span class="font-medium text-slate-600" x-text="formatRupiah(item.harga)"></span>
                                        </template>
                                    </td>

                                    <td class="text-right p-3 font-bold text-slate-900" x-text="formatRupiah(item.qty * item.harga)"></td>
                                    <td class="text-center p-3">
                                        <button @click="removeItem(item.id)" class="text-slate-400 hover:text-rose-600 p-1 rounded-md transition">
                                            <i class="ri-delete-bin-line text-base"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="cart.length === 0">
                                <td colspan="6" class="text-center py-24 text-slate-400 italic text-sm bg-slate-50/20">
                                    Cart masih kosong
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Bagian Bawah: Catatan, Total & Simpan -->
                <div class="mt-4 md:mt-6 grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5 pt-3 md:pt-4 border-t border-slate-100 items-start w-full">
                    
                    <!-- Form Catatan -->
                    <div>
                        <label class="block text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-1">Catatan Pesanan / Pengerjaan</label>
                        <textarea x-model="catatan" rows="2" placeholder="Misal: 'desain banner 3x5, finishing mata ayam'..." class="w-full border border-slate-300 rounded-lg p-2.5 text-xs md:text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none shadow-sm placeholder-slate-400"></textarea>
                    </div>

                    <!-- Ringkasan Nilai & Aksi Simpan -->
                    <div class="space-y-3 md:space-y-4">
                        <div class="flex justify-between items-center py-1.5 border-b border-slate-100">
                            <span class="font-semibold text-slate-500 text-xs md:text-sm">Total Biaya Jasa</span>
                            <span class="font-black text-xl md:text-2xl text-slate-900" x-text="formatRupiah(subtotal)"></span>
                        </div>

                        <button @click="saveTransaction()" class="w-full bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white py-3.5 rounded-xl font-bold transition flex justify-center items-center shadow-lg shadow-indigo-100 text-sm md:text-base tracking-wide cursor-pointer">
                            <i class="ri-save-3-line mr-2 text-lg"></i> F10 Simpan Pesanan Jasa
                        </button>
                    </div>

                </div>

            </div>
        </div>

    </div>

    <!-- MODAL CEK HARGA (F3) -->
    <div
        x-show="showPriceModal"
        x-cloak
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-3"
        @keydown.escape.window="closePriceModal()"
    >
        <div class="bg-white rounded-xl p-4 md:p-6 w-full max-w-xl shadow-2xl">
            <div class="flex justify-between mb-3 md:mb-4 items-center">
                <h3 class="font-bold text-lg md:text-xl text-slate-800">Cek Harga Jasa</h3>
                <button @click="closePriceModal()" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
            </div>

            <input
                x-ref="priceInput"
                x-model="priceSearch"
                @input="searchPrice()"
                placeholder="Scan Barcode / Nama Layanan Jasa..."
                class="w-full border rounded-lg p-3 text-sm outline-none focus:border-indigo-500 shadow-sm"
            >

            <div class="mt-3 max-h-72 overflow-y-auto divide-y divide-slate-100">
                <template x-for="item in priceResults" :key="item.id">
                    <div class="py-2.5">
                        <div class="font-semibold text-slate-800 text-sm" x-text="item.name || item.nama_barang"></div>
                        <div class="text-indigo-600 font-bold text-xs" x-text="'Tarif : Rp ' + Number(item.price || item.harga).toLocaleString('id-ID')"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH PELANGGAN BARU -->
    <div x-data="customerModal" 
        @open-customer-modal.window="showCustomerModal = true; $nextTick(() => $refs.newCustomerName.focus())" 
        x-show="showCustomerModal" 
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-3" 
        @keydown.escape.window="showCustomerModal = false" 
        style="display: none;">

        <div class="bg-white rounded-xl p-4 md:p-6 w-full max-w-md shadow-2xl" @click.outside="showCustomerModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg md:text-xl text-slate-800 flex items-center gap-1">
                    <i class="ri-user-add-line text-indigo-600"></i> Tambah Pelanggan Baru
                </h3>
                <button type="button" @click="showCustomerModal = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">✕</button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input 
                        x-ref="newCustomerName"
                        type="text" 
                        x-model="newCustomer.nama" 
                        @keydown.enter.prevent="$refs.newCustomerPhone.focus()"
                        placeholder="Masukkan nama pelanggan..." 
                        class="w-full border rounded-xl p-2.5 text-sm focus:border-indigo-500 outline-none"
                    >
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">No. Telepon / HP</label>
                    <input 
                        x-ref="newCustomerPhone"
                        type="text" 
                        x-model="newCustomer.telepon" 
                        @keydown.enter.prevent="saveNewCustomer()"
                        placeholder="Contoh: 081234567xx (Opsional)" 
                        class="w-full border rounded-xl p-2.5 text-sm focus:border-indigo-500 outline-none"
                    >
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea 
                        x-ref="newCustomerAlamat"
                        x-model="newCustomer.alamat" 
                        rows="2"
                        placeholder="Masukkan alamat pelanggan... (Opsional)" 
                        class="w-full border rounded-xl p-2.5 text-sm focus:border-indigo-500 outline-none"
                    ></textarea>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button 
                    type="button" 
                    @click="showCustomerModal = false" 
                    class="px-4 py-2 border rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50"
                >
                    Batal
                </button>
                <button 
                    type="button" 
                    @click="saveNewCustomer()" 
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium shadow-sm flex items-center gap-1"
                >
                    <i class="ri-save-3-line"></i> Simpan Pelanggan
                </button>
            </div>
        </div>
    </div>
    
</div>

<script>
function posJasa() {
    return {
        search: '',
        jasaItems: [],
        cart: [],
        subtotal: 0,
        catatan: '',
        selectedIndex: 0,

        // Customer State
        customerSearch: '',
        customerResults: [],
        customerIndex: -1,
        selectedCustomer: null,

        // Price Modal State
        showPriceModal: false,
        priceSearch: '',
        priceResults: [],

        allProducts: window.ALL_JASA_PRODUCTS || [],
        allCustomers: window.ALL_CUSTOMERS || [],

        init() {
            window.addEventListener('customer-added', (e) => {
                const newCustomer = e.detail;
                if (window.ALL_CUSTOMERS) window.ALL_CUSTOMERS.push(newCustomer);
                if (this.allCustomers) this.allCustomers.push(newCustomer);
                this.selectCustomer(newCustomer);
            });

            window.addEventListener('keydown', this.handleShortcut.bind(this));

            this.$nextTick(() => {
                if (this.$refs.jasaInput) this.$refs.jasaInput.focus();
            });
            this.recalculate();
        },

        handleShortcut(e) {
            if (typeof Swal !== 'undefined' && Swal.isVisible()) return;

            if (e.key === 'F2') {
                e.preventDefault();
                this.$refs.jasaInput?.focus();
                this.$refs.jasaInput?.select();
            } else if (e.key === 'F3') {
                e.preventDefault();
                this.showPriceModal = true;
                setTimeout(() => { this.$refs.priceInput?.focus(); }, 50);
            } else if (e.key === 'F8') {
                e.preventDefault();
                this.$refs.customerInput?.focus();
                this.$refs.customerInput?.select();
            } else if (e.key === 'F10') {
                e.preventDefault();
                this.saveTransaction();
            } else if (e.ctrlKey && e.key === 'Delete') {
                e.preventDefault();
                this.clearCart();
            }
        },

        closePriceModal() {
            this.showPriceModal = false;
            this.priceSearch = '';
            this.priceResults = [];
            setTimeout(() => {
                this.$refs.jasaInput?.focus();
            }, 50);
        },

        searchPrice() {
            let q = this.priceSearch.toLowerCase().trim();
            if (q.length < 1) {
                this.priceResults = [];
                return;
            }
            this.priceResults = this.allProducts.filter(item => 
                item.type === 'jasa' && (
                    (item.name || '').toLowerCase().includes(q) ||
                    (item.barcode || '').toLowerCase().includes(q)
                )
            ).slice(0, 10);
        },

        async clearCart() {
            const result = await Swal.fire({
                icon: 'warning',
                title: 'Kosongkan Cart?',
                text: 'Semua item pesanan jasa akan dihapus',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                returnFocus: false
            });

            if (result.isConfirmed) {
                this.cart = [];
                this.catatan = '';
                this.$nextTick(() => {
                    this.recalculate();
                    this.$refs.jasaInput?.focus();
                });
            }
        },

        searchJasa() {
            let q = this.search.toLowerCase().trim();
            if (q.length < 1) {
                this.jasaItems = [];
                return;
            }
            this.jasaItems = this.allProducts.filter(item => 
                item.type === 'jasa' && (
                    (item.name || '').toLowerCase().includes(q) ||
                    (item.barcode || '').toLowerCase().includes(q)
                )
            ).slice(0, 10);
            this.selectedIndex = 0;
        },

        addToCart(jasa) {
            let found = this.cart.find(item => item.id === jasa.id);
            if (found) {
                found.qty++;
            } else {
                this.cart.push({
                    id: jasa.id,
                    nama_barang: jasa.name,
                    harga: Number(jasa.price),
                    qty: 1,
                    is_custom: Boolean(jasa.is_custom_price)
                });
            }
            this.search = '';
            this.jasaItems = [];
            this.selectedIndex = 0;
            this.recalculate();
            this.$nextTick(() => {
                if (this.$refs.jasaInput) this.$refs.jasaInput.focus();
            });
        },

        validateQty(item) {
            item.qty = parseInt(item.qty);
            if (isNaN(item.qty) || item.qty < 1) item.qty = 1;
        },

        validateHarga(item) {
            item.harga = parseInt(item.harga);
            if (isNaN(item.harga) || item.harga < 0) item.harga = 0;
            this.recalculate();
        },

        calculateItem(item) {
            this.validateQty(item);
            this.recalculate();
        },

        removeItem(id) {
            this.cart = this.cart.filter(item => item.id !== id);
            this.recalculate();
        },

        recalculate() {
            this.subtotal = this.cart.reduce((total, item) => total + (Number(item.qty) * Number(item.harga)), 0);
        },

        formatRupiah(val) {
            return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
        },

        // CUSTOMER CONTROLS
        searchCustomer() {
            let keyword = this.customerSearch.toLowerCase().trim();
            if (keyword.length < 2) {
                this.customerResults = [];
                return;
            }
            this.customerResults = this.allCustomers.filter(c => 
                c.nama.toLowerCase().includes(keyword) || 
                (c.kode_pelanggan || '').toLowerCase().includes(keyword)
            ).slice(0, 5);
            this.customerIndex = -1;
        },

        moveCustomerDown() {
            if(this.customerResults.length && this.customerIndex < this.customerResults.length - 1) this.customerIndex++;
        },

        moveCustomerUp() {
            if(this.customerResults.length && this.customerIndex > 0) this.customerIndex--;
        },

        chooseCustomer() {
            if(this.customerIndex >= 0) this.selectCustomer(this.customerResults[this.customerIndex]);
        },

        selectCustomer(customer) {
            this.selectedCustomer = customer;
            this.customerSearch = customer.nama;
            this.customerResults = [];
            this.$nextTick(() => {
                if (this.$refs.jasaInput) this.$refs.jasaInput.focus();
            });
        },

        clearCustomer() {
            this.selectedCustomer = null;
            this.customerSearch = '';
            this.$nextTick(() => {
                if (this.$refs.jasaInput) this.$refs.jasaInput.focus();
            });
        },

        async saveTransaction() {
            if (this.cart.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Keranjang Kosong', text: 'Pilih minimal satu layanan jasa terlebih dahulu!' });
                return;
            }

            const confirmSubmit = await Swal.fire({
                title: 'Simpan Pesanan Jasa?',
                text: "Pesanan ini akan disimpan ke sistem.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonColor: '#6366f1'
            });

            if (!confirmSubmit.isConfirmed) return;

            let response = await fetch("{{ url('/api/pesanan-jasa') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    pelanggan: this.selectedCustomer ? this.selectedCustomer.kode_pelanggan : null,
                    cart: this.cart,
                    catatan: this.catatan
                })
            });

            let result = await response.json();
            if (result.success) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: result.no_nota, timer: 1500, showConfirmButton: false });
                setTimeout(() => { location.reload(); }, 1500);
            } else {
                Swal.fire({ icon: 'error', title: 'Gagal', text: result.message });
            }
        }
    }
}

window.ALL_JASA_PRODUCTS = @json($products);
window.ALL_CUSTOMERS = @json($customers);
</script>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('customerModal', () => ({
            showCustomerModal: false,
            newCustomer: { nama: '', telepon: '', alamat: '' },

            async saveNewCustomer() {
                if (!this.newCustomer.nama.trim()) {
                    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Nama pelanggan wajib diisi!' });
                    return;
                }

                try {
                    let response = await fetch("{{ url('/api/customers') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(this.newCustomer)
                    });

                    let result = await response.json();

                    if (result.success) {
                        window.dispatchEvent(new CustomEvent('customer-added', { detail: result.customer }));
                        this.newCustomer = { nama: '', telepon: '', alamat: '' };
                        this.showCustomerModal = false;
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Pelanggan berhasil disimpan', timer: 1500, showConfirmButton: false });
                    } else {
                        Swal.fire('Error', result.message || 'Gagal menyimpan pelanggan', 'error');
                    }
                } catch (error) {
                    console.error(error);
                    Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
                }
            }
        }));
    });
</script>
@endsection