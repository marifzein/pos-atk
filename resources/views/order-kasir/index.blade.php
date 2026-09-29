@extends('layouts.app')

@section('title', 'Pesanan Kasir - ' . ($branchName ?? 'Cabang'))

@section('content')

<x-page-header 
    title="Pesanan Kasir - {{ $branchName }}" 
    subtitle="Buat Surat Pesanan Jasa & Penerimaan DP" 
    subtitleColor="text-emerald-600"
>
    <x-slot:action>
        <a href="{{ route('kasir.index') }}">
            <x-button color="gray" type="button">
                <i class="ri-arrow-left-line"></i> Kembali
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

<!-- MAIN CONTAINER -->
<div class="max-w-7xl mx-auto p-4" x-data="orderKasir()">

    <div class="grid grid-cols-12 gap-4">

        <!-- ==================== SIDEBAR KIRI ==================== -->
        <div class="col-span-3 space-y-4">

            <!-- INFORMASI WO & DEADLINE -->
            <div class="bg-white rounded-xl shadow p-4 space-y-3">
                <h2 class="font-bold text-lg border-b pb-2 text-slate-800">Informasi Pesanan</h2>

                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-2.5">
                    <label class="text-xs font-semibold text-indigo-600 uppercase block">No. Pesanan</label>
                    <span class="text-base font-bold text-indigo-950 font-mono">{{ $previewSp }}</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">
                        Tgl Estimasi Selesai <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="datetime-local" 
                        x-model="tglEstimasiSelesai" 
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-sm focus:border-indigo-500 outline-none text-slate-700 font-medium"
                    >
                    <div class="flex gap-1 mt-1.5">
                        <button type="button" @click="setQuickTime(3)" class="text-[11px] bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 px-2 py-1 rounded border border-slate-200 transition">+3 Jam</button>
                        <button type="button" @click="setQuickTime(24)" class="text-[11px] bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 px-2 py-1 rounded border border-slate-200 transition">Besok</button>
                        <button type="button" @click="setQuickTime(48)" class="text-[11px] bg-slate-100 hover:bg-indigo-50 hover:text-indigo-600 text-slate-600 px-2 py-1 rounded border border-slate-200 transition">2 Hari</button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase mb-1">Catatan Pengerjaan</label>
                    <textarea 
                        x-model="catatanOrder" 
                        rows="2" 
                        placeholder="Detail spesifikasi / finishing..."
                        class="w-full rounded-xl border border-slate-300 p-2.5 text-xs focus:border-indigo-500 outline-none text-slate-700"
                    ></textarea>
                </div>
            </div>

            <!-- PELANGGAN -->
            <div class="bg-white rounded-xl shadow p-4">
                <label class="block text-sm font-semibold mb-2 text-slate-800">Pelanggan <span class="text-red-500">*</span></label>
                <button type="button" @click="$dispatch('open-customer-modal')" class="text-indigo-600 text-sm hover:underline mb-1 inline-block">
                    + Tambah Pelanggan Baru
                </button>
                
                <div class="relative" @click.outside="customerResults=[]">
                    <input
                        x-ref="customerInput"
                        type="text"
                        x-model="customerSearch"
                        @keydown.arrow-down.prevent="moveCustomerDown()"
                        @keydown.arrow-up.prevent="moveCustomerUp()"
                        @keydown.enter.prevent="chooseCustomer()"
                        @keydown.escape.prevent="closeCustomerSearch()"
                        @input="searchCustomer()"
                        placeholder="Cari nama / kode pelanggan"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-indigo-500 outline-none text-sm"
                    >

                    <div x-show="customerResults.length" x-cloak class="absolute left-0 right-0 top-full mt-1 bg-white border rounded-xl shadow-lg z-50 max-h-72 overflow-y-auto">
                        <template x-for="(customer, index) in customerResults" :key="customer.id || index">
                            <div
                                @click="selectCustomer(customer)"
                                :class="customerIndex === index ? 'bg-indigo-100' : ''"
                                class="px-4 py-3 cursor-pointer hover:bg-indigo-50 border-b transition-colors"
                            >
                                <div class="font-semibold text-slate-800" x-text="customer.nama"></div>
                                <div class="text-xs text-slate-500" x-text="customer.alamat || customer.telepon || 'Tidak ada alamat/telp'"></div>
                            </div>
                        </template>
                    </div>
                </div>

                <template x-if="selectedCustomer">
                    <div class="mt-3">
                        <div class="rounded-xl bg-indigo-50 p-3 border border-indigo-100">
                            <div class="font-semibold text-indigo-950" x-text="selectedCustomer.nama"></div>
                            <div class="text-xs text-slate-600 mt-0.5" x-text="selectedCustomer.alamat || 'Alamat tidak diisi'"></div>
                            <div class="text-xs text-slate-400" x-show="selectedCustomer.telepon" x-text="'Telp: ' + selectedCustomer.telepon"></div>
                            <button @click="clearCustomer()" class="mt-2 text-red-600 hover:text-red-700 text-xs font-semibold flex items-center gap-1">
                                <i class="ri-close-circle-line"></i> Kosongkan Pelanggan
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- SHORTCUT KEYBOARD -->
            <div class="bg-white rounded-xl shadow p-4">
                <h2 class="font-bold mb-3 text-slate-800">Shortcut Keyboard</h2>
                <div class="space-y-2 text-sm text-slate-600">
                    <div class="flex justify-between"><span>F2</span><span class="font-medium text-slate-800">Barcode/Jasa</span></div>
                    <div class="flex justify-between"><span>F4</span><span class="font-medium text-slate-800">Bayar / DP</span></div>
                    <div class="flex justify-between"><span>F8</span><span class="font-medium text-slate-800">Pelanggan</span></div>
                    <div class="flex justify-between"><span>F10</span><span class="font-medium text-indigo-600 font-bold">Simpan Pesanan</span></div>
                    <div class="flex justify-between text-rose-600"><span>Ctrl+Del</span><span class="font-semibold">Kosongkan Cart</span></div>
                </div>
            </div>

        </div>

        <!-- ==================== AREA KANAN ==================== -->
        <div class="col-span-9">
            <div class="bg-white rounded-xl shadow min-h-[580px] flex flex-col justify-between">

                <div>
                    <!-- SEARCH BARCODE / ITEM -->
                    <div class="border-b p-4">
                        <div class="relative">
                            <input
                                id="barcodeInput"
                                x-ref="barcodeInput"
                                type="text"
                                x-model="search"
                                @input="searchProduct"
                                @keydown.arrow-down.prevent="if(selectedIndex < products.length - 1) selectedIndex++"
                                @keydown.arrow-up.prevent="if(selectedIndex > 0) selectedIndex--"
                                @keydown.enter.prevent="if(products.length) addToCart(products[selectedIndex])"
                                placeholder="Scan Barcode / Kode Jasa / Nama Barang..."
                                class="w-full border rounded-xl p-3 text-lg focus:border-indigo-500 outline-none"
                            >

                            <div x-show="products.length" x-cloak class="absolute left-0 right-0 bg-white border rounded-lg shadow-xl mt-1 z-50 max-h-64 overflow-y-auto">
                                <template x-for="(product, index) in products" :key="product.id">
                                    <div
                                        @click="addToCart(product)"
                                        :class="selectedIndex === index ? 'bg-indigo-100' : ''"
                                        class="p-3 border-b cursor-pointer hover:bg-slate-50 transition-colors"
                                    >
                                        <div class="font-medium text-slate-800" x-text="product.name"></div>
                                        <div class="text-sm text-gray-500" x-text="product.sku"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- CART TABLE -->
                    <div class="p-4">
                        <h2 class="font-bold text-lg mb-4 text-slate-800">Rincian Barang & Pekerjaan Jasa</h2>
                        <div class="border rounded-lg overflow-hidden">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50 text-slate-600 text-sm">
                                        <th class="text-center p-3 w-12">No</th>
                                        <th class="text-left p-3">Barang / Jasa</th>
                                        <th class="p-3 w-28 text-center">Qty</th>
                                        <th class="text-right p-3 w-40">Harga</th>
                                        <th class="text-right p-3 w-36">Total</th>
                                        <th class="w-16 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="(item, index) in cart" :key="item.id">
                                        <tr class="hover:bg-slate-50/60 transition">
                                            <td class="p-3 text-center text-gray-500 font-medium bg-gray-50/50" x-text="index + 1"></td>
                                            <td class="p-3 font-semibold text-slate-800">
                                                <div x-text="item.nama_barang"></div>
                                                <input 
                                                    type="text" 
                                                    x-model="item.notes" 
                                                    placeholder="Catatan pengerjaan item..." 
                                                    class="w-full mt-1 border-b border-dashed border-slate-300 text-xs text-slate-600 focus:border-indigo-500 outline-none pb-0.5 bg-transparent"
                                                >
                                            </td>
                                            <td class="p-3 text-center">
                                                <input
                                                    type="number"
                                                    min="1"
                                                    x-model="item.qty"
                                                    @change="validateQty(item)"
                                                    @keydown.enter.prevent="$refs.barcodeInput.focus()"
                                                    @input="calculateItem(item)"
                                                    class="w-20 border rounded text-center p-1.5 font-bold focus:border-indigo-500 outline-none"
                                                />
                                            </td>
                                            <td class="text-right p-3 font-mono">
                                                <template x-if="item.is_custom_price == true || item.is_custom_price == 1">
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        x-model.number="item.harga"
                                                        @input="recalculate()"
                                                        class="w-28 text-right border border-amber-400 bg-amber-50 rounded px-2 py-1 font-mono text-slate-800 font-bold focus:border-indigo-500 outline-none"
                                                    >
                                                </template>
                                                <template x-if="!(item.is_custom_price == true || item.is_custom_price == 1)">
                                                    <span x-text="formatRupiah(item.harga)"></span>
                                                </template>
                                            </td>
                                            <td class="text-right p-3 font-bold font-mono text-slate-900" x-text="formatRupiah(item.qty * item.harga)"></td>
                                            <td class="text-center p-3">
                                                <button @click="removeItem(item.id)" class="text-red-500 hover:text-red-700 p-1">
                                                    <i class="ri-delete-bin-line text-lg"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>

                                    <tr x-show="cart.length === 0">
                                        <td colspan="6" class="text-center p-14 text-gray-400 italic">
                                            Cart masih kosong. Scan barcode atau cari item di atas.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- FOOTER KANAN: PANEL PEMBAYARAN DP & WO -->
                <div class="border-t p-4 flex justify-end bg-slate-50/30 rounded-b-xl">
                    <div class="w-full md:w-[460px]">
                        <div class="bg-white rounded-xl border p-4 shadow-sm space-y-3">
                            
                            <h3 class="text-lg font-bold border-b pb-2 text-slate-800 tracking-wide flex justify-between items-center">
                                <span>PEMBAYARAN / DP</span>
                                <span class="text-xs px-2.5 py-1 rounded-full font-bold uppercase"
                                      :class="{
                                          'bg-slate-100 text-slate-600': statusBayarText === 'UNPAID',
                                          'bg-amber-100 text-amber-800': statusBayarText === 'DP',
                                          'bg-emerald-100 text-emerald-800': statusBayarText === 'LUNAS'
                                      }"
                                      x-text="statusBayarText">
                                </span>
                            </h3>

                            <div class="flex justify-between items-center bg-indigo-50/50 p-2.5 rounded-lg border border-indigo-100">
                                <span class="font-bold text-slate-800">TOTAL TAGIHAN</span>
                                <span class="font-black text-xl text-indigo-600 font-mono" x-text="formatRupiah(totalTagihan)"></span>
                            </div>

                            <!-- Pilihan Metode Pembayaran -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1.5 uppercase">Metode Pembayaran</label>
                                <div class="grid grid-cols-4 gap-1.5 text-xs">
                                    <button type="button" @click="metode = 'cash'" :class="metode === 'cash' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="py-2 rounded-lg border transition text-center">Cash</button>
                                    <button type="button" @click="metode = 'qris'" :class="metode === 'qris' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="py-2 rounded-lg border transition text-center">QRIS</button>
                                    <button type="button" @click="metode = 'card'" :class="metode === 'card' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="py-2 rounded-lg border transition text-center">Card EDC</button>
                                    <button type="button" @click="metode = 'transfer'" :class="metode === 'transfer' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'" class="py-2 rounded-lg border transition text-center">Transfer</button>
                                </div>
                            </div>

                            <!-- Field Non-Tunai Tambahan -->
                            <div x-show="metode !== 'cash'" x-cloak class="grid grid-cols-2 gap-2 bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                                <div>
                                    <label class="block text-[11px] font-medium text-slate-600 mb-0.5">Bank / E-Wallet</label>
                                    <input type="text" x-model="bankName" placeholder="BCA / BRI / Gopay" class="w-full text-xs rounded border border-slate-300 p-1.5 outline-none focus:border-indigo-500 bg-white">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-medium text-slate-600 mb-0.5">No. Referensi / RRN</label>
                                    <input type="text" x-model="refNo" placeholder="Kode approval..." class="w-full text-xs rounded border border-slate-300 p-1.5 outline-none focus:border-indigo-500 bg-white">
                                </div>
                            </div>

                            <hr class="border-slate-100">

                            <!-- Nominal Pembayaran / DP -->
                            <div class="flex justify-between items-center">
                                <label class="font-medium text-slate-700">Nominal Bayar (F4)</label>
                                <input
                                    id="nominalBayarInput"
                                    type="number"
                                    min="0"
                                    x-model.number="nominalBayar"
                                    @input="recalculate()"
                                    class="text-right rounded-xl border border-slate-300 hover:border-slate-400 w-44 bg-white px-3 py-1.5 font-mono text-slate-900 font-bold focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all text-lg"
                                >
                            </div>

                            <!-- Quick Button Nominal -->
                            <div class="grid grid-cols-3 gap-1.5">
                                <button type="button" @click="setNominal(0)" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 py-1.5 rounded-lg border font-medium">Tanpa DP (Rp 0)</button>
                                <button type="button" @click="setNominal(totalTagihan * 0.5)" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 py-1.5 rounded-lg border font-medium">DP 50%</button>
                                <button type="button" @click="setNominal(totalTagihan)" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 py-1.5 rounded-lg border font-medium">Langsung Lunas</button>
                            </div>

                            <hr class="border-slate-100">

                            <div class="flex justify-between items-center text-base font-bold">
                                <span :class="sisaTagihan > 0 ? 'text-red-600' : 'text-slate-700'">Sisa Pelunasan Nanti</span>
                                <span class="font-mono text-lg" :class="sisaTagihan > 0 ? 'text-red-600' : 'text-slate-700'" x-text="formatRupiah(sisaTagihan)"></span>
                            </div>

                            <button
                                @click="saveOrder()"
                                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3.5 rounded-xl font-bold shadow-md transition flex items-center justify-center gap-2 text-base cursor-pointer mt-2"
                            >
                                <i class="ri-printer-line text-xl"></i>
                                {{-- <span x-text="nominalBayar > 0 ? 'Simpan & Terbitkan SP (F10)' : 'Simpan Order WO (F10)'"></span> --}}
                                <span>Simpan & Terbitkan SP (F10)</span>
                            </button>
                            

                        </div>    
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- MODAL TAMBAH PELANGGAN BARU (IDENTIK DENGAN POS) -->
    <div x-data="customerModal" 
        @open-customer-modal.window="showCustomerModal = true; $nextTick(() => $refs.newCustomerName.focus())" 
        x-show="showCustomerModal" 
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" 
        @keydown.escape.window="showCustomerModal = false" 
        style="display: none;">

        <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-2xl" @click.outside="showCustomerModal = false">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-xl text-slate-800 flex items-center gap-1">
                    <i class="ri-user-add-line text-indigo-600"></i> Tambah Pelanggan Baru
                </h3>
                <button type="button" @click="showCustomerModal = false" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input 
                        x-ref="newCustomerName"
                        type="text" 
                        x-model="newCustomer.nama" 
                        @keydown.enter.prevent="$refs.newCustomerPhone.focus()"
                        placeholder="Masukkan nama pelanggan..." 
                        class="w-full border rounded-xl p-3 text-sm focus:border-indigo-500 outline-none"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon / HP</label>
                    <input 
                        x-ref="newCustomerPhone"
                        type="text" 
                        x-model="newCustomer.telepon" 
                        @keydown.enter.prevent="saveNewCustomer()"
                        placeholder="Contoh: 081234567xx (Opsional)" 
                        class="w-full border rounded-xl p-3 text-sm focus:border-indigo-500 outline-none"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea 
                        x-ref="newCustomerAlamat"
                        x-model="newCustomer.alamat" 
                        rows="2"
                        placeholder="Masukkan alamat pelanggan... (Opsional)" 
                        class="w-full border rounded-xl p-3 text-sm focus:border-indigo-500 outline-none"
                    ></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
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
function orderKasir() {
    return {
        search: '',
        products: [],
        selectedIndex: 0,
        cart: [],

        // Customer State
        customerSearch: '',
        customerResults: [],
        customerIndex: -1,
        selectedCustomer: null,
        allCustomers: window.ALL_CUSTOMERS || [],
        allProducts: window.ALL_PRODUCTS || [],

        // Order State
        tglEstimasiSelesai: '',
        catatanOrder: '',

        // Payment State
        totalTagihan: 0,
        nominalBayar: 0,
        sisaTagihan: 0,
        metode: 'cash',
        bankName: '',
        refNo: '',

        init() {
            window.addEventListener('customer-added', (e) => {
                const newCustomer = e.detail;
                if (window.ALL_CUSTOMERS) window.ALL_CUSTOMERS.push(newCustomer);
                if (this.allCustomers) this.allCustomers.push(newCustomer);
                this.selectCustomer(newCustomer);
            });

            window.addEventListener('keydown', this.handleShortcut.bind(this));

            this.setQuickTime(24); // default besok

            this.$nextTick(() => {
                this.$refs.barcodeInput?.focus();
            });

            this.recalculate();
        },

        get statusBayarText() {
            if (this.nominalBayar <= 0) return 'UNPAID';
            if (this.nominalBayar < this.totalTagihan) return 'DP';
            return 'LUNAS';
        },

        setQuickTime(hours) {
            const d = new Date();
            d.setHours(d.getHours() + hours);
            d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
            this.tglEstimasiSelesai = d.toISOString().slice(0, 16);
        },

        recalculate() {
            this.totalTagihan = this.cart.reduce((total, item) => total + (Number(item.qty) * Number(item.harga)), 0);
            this.sisaTagihan = Math.max(0, this.totalTagihan - Number(this.nominalBayar || 0));
        },

        setNominal(val) {
            this.nominalBayar = Math.min(this.totalTagihan, Number(val));
            this.recalculate();
        },

        addToCart(product) {
            let found = this.cart.find(item => item.id === product.id);

            if (found) {
                found.qty++;
            } else {
                let isCustom = product.is_custom_price == true || product.is_custom_price == 1;
                this.cart.push({
                    id: product.id,
                    kode_barang: product.sku,
                    nama_barang: product.name,
                    purchase_price: product.purchase_price || 0,
                    harga: Number(product.price || 0),
                    qty: 1,
                    notes: '',
                    is_custom_price: isCustom
                });
            }

            this.search = '';
            this.products = [];
            this.selectedIndex = 0;
            this.recalculate();

            this.$nextTick(() => {
                document.getElementById('barcodeInput')?.focus();
            });
        },

        removeItem(id) {
            this.cart = this.cart.filter(item => item.id !== id);
            this.$nextTick(() => this.recalculate());
        },

        validateQty(item) {
            item.qty = parseInt(item.qty);
            if (isNaN(item.qty) || item.qty < 1) item.qty = 1;
        },

        calculateItem(item) {
            item.qty = Number(item.qty);
            this.$nextTick(() => this.recalculate());
        },

        searchProduct() {
            let q = this.search.toLowerCase().trim();
            if (q.length < 1) {
                this.products = [];
                return;
            }
            this.products = this.allProducts.filter(product =>
                (product.name || '').toLowerCase().includes(q) ||
                (product.sku || '').toLowerCase().includes(q) ||
                (product.barcode || '').toLowerCase().includes(q)
            ).slice(0, 10);
            this.selectedIndex = 0;
        },

        searchCustomer() {
            let keyword = this.customerSearch.toLowerCase().trim();
            if (keyword.length < 2) {
                this.customerResults = [];
                this.customerIndex = -1;
                return;
            }
            this.customerResults = this.allCustomers.filter(c =>
                c.nama.toLowerCase().includes(keyword) ||
                (c.kode_pelanggan || '').toLowerCase().includes(keyword)
            ).slice(0, 8);
            this.customerIndex = -1;
        },

        moveCustomerDown() {
            if (this.customerResults.length === 0) return;
            if (this.customerIndex < this.customerResults.length - 1) this.customerIndex++;
        },

        moveCustomerUp() {
            if (this.customerResults.length === 0) return;
            if (this.customerIndex > 0) this.customerIndex--;
        },

        chooseCustomer() {
            if (this.customerIndex < 0) return;
            this.selectCustomer(this.customerResults[this.customerIndex]);
        },

        selectCustomer(customer) {
            this.selectedCustomer = customer;
            this.customerSearch = customer.nama;
            this.customerResults = [];
            this.customerIndex = -1;
            this.$nextTick(() => {
                document.getElementById('barcodeInput')?.focus();
            });
        },

        closeCustomerSearch() {
            this.customerResults = [];
            this.customerIndex = -1;
            this.$nextTick(() => {
                this.$refs.barcodeInput?.focus();
            });
        },

        clearCustomer() {
            this.selectedCustomer = null;
            this.customerSearch = '';
            this.customerResults = [];
            this.$nextTick(() => {
                document.getElementById('barcodeInput')?.focus();
            });
        },

        handleShortcut(e) {
            if (typeof Swal !== 'undefined' && Swal.isVisible()) return;

            if (e.key === 'F2') {
                e.preventDefault();
                this.$refs.barcodeInput?.focus();
                this.$refs.barcodeInput?.select();
            } else if (e.key === 'F4') {
                e.preventDefault();
                document.getElementById('nominalBayarInput')?.focus();
                document.getElementById('nominalBayarInput')?.select();
            } else if (e.key === 'F8') {
                e.preventDefault();
                this.$refs.customerInput?.focus();
                this.$refs.customerInput?.select();
            } else if (e.key === 'F10') {
                e.preventDefault();
                this.saveOrder();
            } else if (e.ctrlKey && e.key === 'Delete') {
                e.preventDefault();
                this.cart = [];
                this.nominalBayar = 0;
                this.recalculate();
            }
        },

        formatRupiah(value) {
            return Number(value || 0).toLocaleString('id-ID');
        },

        async saveOrder() {
            if (this.cart.length === 0) {
                await Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Keranjang order masih kosong!', returnFocus: false });
                this.$refs.barcodeInput?.focus();
                return;
            }

            if (!this.selectedCustomer) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Pelanggan Wajib Dipilih',
                    text: 'Untuk Surat Pesanan, silakan pilih pelanggan terlebih dahulu!',
                    returnFocus: false
                });
                this.$refs.customerInput?.focus();
                return;
            }

            if (!this.tglEstimasiSelesai) {
                await Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Tentukan tanggal perkiraan selesai!', returnFocus: false });
                return;
            }

            const konfirmasi = await Swal.fire({
                title: this.nominalBayar > 0 ? 'Terbitkan Surat Pesanan?' : 'Simpan Surat Pesanan?',
                text: 'Pesanan akan dikirim ke antrean produksi untuk diproses',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya (Enter)',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#4f46e5',
                returnFocus: false
            });

            if (!konfirmasi.isConfirmed) return;

            try {
                let response = await fetch("{{ route('order-kasir.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        customer_id: this.selectedCustomer.id,
                        tgl_estimasi_selesai: this.tglEstimasiSelesai,
                        catatan: this.catatanOrder,
                        nominal_bayar: Number(this.nominalBayar || 0),
                        metode_pembayaran: this.metode,
                        bank_name: this.bankName,
                        ref_no: this.refNo,
                        items: this.cart.map(item => ({
                            product_id: item.id,
                            name: item.nama_barang,
                            qty: item.qty,
                            price: item.harga,
                            purchase_price: item.purchase_price,
                            notes: item.notes
                        }))
                    })
                });

                let result = await response.json();

                if (result.status === 'success') {
                    // 1. Langsung buka tab baru untuk print SP
                    const printUrl = `{{ url('/order-kasir') }}/${result.order_id}/print`;
                    window.open(printUrl, '_blank');

                    // 2. Beri notifikasi berhasil
                    await Swal.fire({ 
                        title: 'Berhasil!', 
                        text: `Surat Pesanan: ${result.no_pesanan}`, 
                        icon: 'success', 
                        timer: 1500, 
                        showConfirmButton: false 
                    });

                    // 3. Reset halaman untuk pesanan kasir berikutnya
                    window.location.reload();
                } else {
                    await Swal.fire({ icon: 'error', title: 'Gagal', text: result.message, returnFocus: false });
                }
                
            } catch (e) {
                console.error(e);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem saat menyimpan' });
            }
        }
    }
}

window.ALL_PRODUCTS = @json($products ?? []);
window.ALL_CUSTOMERS = @json($customers ?? []);
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