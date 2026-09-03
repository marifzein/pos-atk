@extends('layouts.app')

@section('title','POS - Point of Sales')

@section('content')

    

    <x-page-header title="POS/Kasir" subtitle="Buat dan Cetak Nota">
    <x-slot:action>
        <a href="{{ route('kasir.index') }}">
            <x-button color="gray" type="button">
                <i class="ri-arrow-left-line"></i> Kembali
            </x-button>
        </a>
    </x-slot:action>
</x-page-header>

    <!-- MAIN CONTAINER -->
    <div class="max-w-7xl mx-auto p-4" x-data="posKasir()">

        <div class="grid grid-cols-12 gap-4">

            <!-- ==================== SIDEBAR KIRI ==================== -->
            <div class="col-span-3 space-y-4">

                <!-- INFORMASI NOTA & WO -->
                <div class="bg-white rounded-xl shadow p-4 space-y-3">
                    <h2 class="font-bold text-lg border-b pb-2">Informasi Transaksi</h2>
                    
                    <template x-if="orderData">
                        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-2.5">
                            <label class="text-xs font-semibold text-indigo-600 uppercase block">No. Pesanan ( Work Order )</label>
                            <span class="text-base font-bold text-indigo-950 font-mono" x-text="orderData.no_pesanan"></span>
                        </div>
                    </template>

                    <div>
                        <label class="text-sm text-gray-500">No Nota</label>
                        <input type="text" value="{{ $noNota }}" readonly class="w-full mt-1 border rounded-lg p-2 bg-gray-50 font-medium font-mono">
                    </div>
                </div>

                <!-- PELANGGAN -->
                <div class="bg-white rounded-xl shadow p-4 mb-4">
                    <label class="block text-sm font-semibold mb-2">Pelanggan</label>
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
                        <div class="flex justify-between"><span>F2</span><span class="font-medium text-slate-800">Barcode</span></div>
                        <div class="flex justify-between"><span>F3</span><span class="font-medium text-slate-800">Cek Harga Barang</span></div>
                        <div class="flex justify-between"><span>F4</span><span class="font-medium text-slate-800">Bayar</span></div>
                        <div class="flex justify-between"><span>F8</span><span class="font-medium text-slate-800">Pelanggan</span></div>
                        <div class="flex justify-between"><span>F10</span><span class="font-medium text-slate-800">Simpan Transaksi</span></div>
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
                                    placeholder="Scan Barcode / Kode Barang / Nama Barang"
                                    class="w-full border rounded-xl p-3 text-lg focus:border-indigo-500 outline-none"
                                >

                                <div x-show="products.length" x-cloak class="absolute left-0 right-0 bg-white border rounded-lg shadow-xl mt-1 z-50 max-h-64 overflow-y-auto">
                                    <template x-for="(product, index) in products" :key="product.id">
                                        <div
                                            @click="addToCart(product)"
                                            :class="selectedIndex === index ? 'bg-indigo-100' : ''"
                                            class="p-3 border-b cursor-pointer hover:bg-slate-50 transition-colors"
                                        >
                                            <div class="font-medium text-slate-800" x-text="product.nama_barang"></div>
                                            <div class="text-sm text-gray-500" x-text="product.kode_barang"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- CART TABLE -->
                        <div class="p-4">
                            <h2 class="font-bold text-lg mb-4 text-slate-800">Keranjang Belanja</h2>
                            <div class="border rounded-lg overflow-hidden">
                                <table class="w-full">
                                    <thead>
                                        <tr class="bg-gray-50 text-slate-600 text-sm">
                                            <th class="text-center p-3 w-12">No</th>
                                            <th class="text-left p-3">Barang</th>
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
                                                    <span x-show="item.is_custom_price" class="text-[10px] bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded font-normal">Harga Custom</span>
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
                                                    <!-- Input editable jika is_custom_price true / 1 -->
                                                    <template x-if="item.is_custom_price == true || item.is_custom_price == 1">
                                                        <input
                                                            type="number"
                                                            min="0"
                                                            x-model.number="item.harga"
                                                            @input="recalculate()"
                                                            class="w-28 text-right border border-amber-400 bg-amber-50 rounded px-2 py-1 font-mono text-slate-800 font-bold focus:border-indigo-500 outline-none"
                                                        >
                                                    </template>

                                                    <!-- Readonly jika barang biasa -->
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
                                                Cart masih kosong
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER KANAN: PANEL PEMBAYARAN LENGKAP -->
                    <div class="border-t p-4 flex justify-end bg-slate-50/30 rounded-b-xl">
                        <div class="w-full md:w-[420px]">
                            <div class="bg-white rounded-xl border p-4 shadow-sm space-y-3">
                                
                                <h3 class="text-lg font-bold border-b pb-2 text-slate-800 tracking-wide">
                                    PEMBAYARAN
                                </h3>

                                <div class="flex justify-between items-center text-slate-700">
                                    <span class="font-medium">Subtotal</span>
                                    <span class="font-bold text-lg font-mono text-slate-900" x-text="formatRupiah(subtotal)"></span>
                                </div>

                                <div class="flex justify-between items-center">
                                    <label class="font-medium text-slate-700">Diskon / Potongan</label>
                                    <input
                                        type="number"
                                        min="0"
                                        x-model.number="diskon"
                                        @input="if(diskon < 0) diskon = 0; recalculate();"
                                        class="text-right rounded-xl border border-slate-300 hover:border-slate-400 w-40 bg-white px-3 py-1.5 font-mono text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all"
                                    >
                                </div>

                                <hr class="border-slate-100">

                                <div class="flex justify-between items-center bg-indigo-50/50 p-2.5 rounded-lg border border-indigo-100">
                                    <span class="font-bold text-slate-800">TOTAL</span>
                                    <span class="font-black text-xl text-indigo-600 font-mono" x-text="formatRupiah(grandTotal)"></span>
                                </div>

                                <hr class="border-slate-100">

                                <div class="flex justify-between items-center">
                                    <label class="font-medium text-slate-700">Cash (F4)</label>
                                    <input
                                        id="cash"
                                        type="number"
                                        min="0"
                                        x-model.number="cash"
                                        @input="recalculate()"
                                        class="text-right rounded-xl border border-slate-300 hover:border-slate-400 w-40 bg-white px-3 py-1.5 font-mono text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all"
                                    >
                                </div>

                                <div class="flex justify-between items-center">
                                    <label class="font-medium text-slate-700">Voucher</label>
                                    <input
                                        type="number"
                                        min="0"
                                        x-model.number="voucher"
                                        @input="if(voucher < 0) voucher = 0; recalculate();"
                                        class="text-right rounded-xl border border-slate-300 hover:border-slate-400 w-40 bg-white px-3 py-1.5 font-mono text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all"
                                    >
                                </div>

                                <div class="flex justify-between items-center">
                                    <label class="font-medium text-slate-700">Card</label>
                                    <input
                                        type="number"
                                        min="0"
                                        x-model.number="card"
                                        @input="if(card < 0) card = 0; recalculate();"
                                        class="text-right rounded-xl border border-slate-300 hover:border-slate-400 w-40 bg-white px-3 py-1.5 font-mono text-slate-700 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100 outline-none transition-all"
                                    >
                                </div>

                                <div class="flex justify-between items-center">
                                    <label class="font-medium text-amber-700">Hutang / Kasbon</label>
                                    <input
                                        type="number"
                                        min="0"
                                        x-model.number="hutang"
                                        @input="recalculate()"
                                        class="text-right rounded-lg border border-amber-300 hover:border-amber-400 w-40 bg-amber-50/50 py-1.5 px-3 font-mono text-amber-900 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 outline-none transition-all font-semibold"
                                    >
                                </div>

                                <hr class="border-slate-100">

                                <div class="flex justify-between items-center text-lg font-bold text-red-600">
                                    <span>Kurang Bayar</span>
                                    <span class="font-mono" :class="kurangBayar > 0 ? 'text-red-600' : 'text-slate-700'" x-text="formatRupiah(kurangBayar)"></span>
                                </div>

                                <div class="flex justify-between items-center text-lg font-bold text-green-600">
                                    <span>Kembalian</span>
                                    <span class="font-mono text-green-600" x-text="formatRupiah(kembalian)"></span>
                                </div>

                                <template x-if="kembalian >= 100000">
                                    <div class="text-right text-xs text-red-600 font-bold mt-1 bg-red-50 p-2 rounded-lg border border-red-200">
                                        <i class="ri-error-warning-line mr-1"></i> Kembalian terlalu besar, Cek ulang angka yang anda input!
                                    </div>
                                </template>

                                <hr class="border-slate-100">

                                <button
                                    @click="saveTransaction()"
                                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3.5 rounded-xl font-bold shadow-md transition flex items-center justify-center gap-2 text-base cursor-pointer"
                                >
                                    <i class="ri-save-line text-xl"></i> Simpan Transaksi
                                </button>

                            </div>    
                        </div>
                    </div>

                </div>
            </div>

        </div>

        <!-- MODAL CEK HARGA (F3) -->
        <div
            x-show="showPriceModal"
            x-cloak
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
            @keydown.escape.window="closePriceModal()"
        >
            <div class="bg-white rounded-xl p-6 w-full max-w-xl shadow-2xl">
                <div class="flex justify-between mb-4 items-center">
                    <h3 class="font-bold text-xl text-slate-800">Cek Harga</h3>
                    <button @click="closePriceModal()" class="text-gray-400 hover:text-gray-600 text-lg">✕</button>
                </div>

                <input
                    x-ref="priceInput"
                    x-model="priceSearch"
                    @input="searchPrice()"
                    placeholder="Scan Barcode / Nama Barang"
                    class="w-full border rounded-lg p-3 outline-none focus:border-indigo-500"
                >

                <div class="mt-4 max-h-80 overflow-y-auto divide-y divide-slate-100">
                    <template x-for="item in priceResults" :key="item.id">
                        <div class="py-3">
                            <div class="font-semibold text-slate-800" x-text="item.name"></div>
                            <div class="text-green-600 font-bold" x-text="'Harga : Rp ' + Number(item.price).toLocaleString('id-ID')"></div>
                            <div class="text-xs text-gray-500" x-text="'Stok : ' + item.stock + ' ' + (item.satuan || '')"></div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- MODAL TAMBAH PELANGGAN BARU -->
        
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
function posKasir() {
    return {
        search: '',
        products: [],
        selectedIndex: 0,
        cart: [],

        // Order / WO State
        orderData: @json($orderData),

        // Payment State
        subtotal: 0,
        diskon: 0,
        cash: 0,
        voucher: 0,
        card: 0,
        hutang: 0,
        paymentTotal: 0,
        kurangBayar: 0,
        kembalian: 0,

        // Customer State
        customerSearch: '',
        customerResults: [],
        customerIndex: -1,
        selectedCustomer: null,
        allCustomers: window.ALL_CUSTOMERS || [],
        allProducts: window.ALL_PRODUCTS || [],

        // Price Modal State
        showPriceModal: false,
        priceSearch: '',
        priceResults: [],

        init() {
            window.addEventListener('customer-added', (e) => {
                const newCustomer = e.detail;
                if (window.ALL_CUSTOMERS) window.ALL_CUSTOMERS.push(newCustomer);
                if (this.allCustomers) this.allCustomers.push(newCustomer);
                this.selectCustomer(newCustomer);
            });

            window.addEventListener('keydown', this.handleShortcut.bind(this));

            // Load items & customer dari WO jika tersedia
            if (this.orderData) {
                if (this.orderData.items && this.orderData.items.length > 0) {
                    this.cart = this.orderData.items.map(item => ({
                        //id: item.id,
                        id: item.product_id,
                        kode_barang: item.kode_barang,
                        nama_barang: item.nama_barang,
                        purchase_price: item.purchase_price || 0,
                        harga: item.harga,
                        qty: item.qty,
                        is_custom_price: item.is_custom_price ?? false
                    }));
                }

                if (this.orderData.customer_id && this.allCustomers.length > 0) {
                    let matchCust = this.allCustomers.find(c => c.id == this.orderData.customer_id);
                    if (matchCust) {
                        this.selectCustomer(matchCust);
                    } else {
                        this.selectedCustomer = { nama: this.orderData.customer_name };
                    }
                } else if (this.orderData.customer_name) {
                    this.selectedCustomer = { nama: this.orderData.customer_name };
                }
            }

            this.$nextTick(() => {
                this.$refs.barcodeInput?.focus();
            });

            this.recalculate();
        },

        get grandTotal() {
            return Math.max(0, this.subtotal - Number(this.diskon || 0));
        },

        recalculate() {
            this.subtotal = this.cart.reduce((total, item) => total + (Number(item.qty) * Number(item.harga)), 0);

            let nilaiCash = Number(this.cash || 0);
            let nilaiVoucher = Number(this.voucher || 0);
            let nilaiCard = Number(this.card || 0);
            let totalBayarRiil = nilaiCash + nilaiVoucher + nilaiCard;

            let sisaTagihan = Math.max(0, this.grandTotal - totalBayarRiil);
            let nilaiHutang = Number(this.hutang || 0);

            if (totalBayarRiil >= this.grandTotal) {
                nilaiHutang = 0;
                this.hutang = 0;
            } else if (nilaiHutang > sisaTagihan) {
                nilaiHutang = sisaTagihan;
                this.hutang = sisaTagihan;
            }

            this.paymentTotal = totalBayarRiil + nilaiHutang;
            this.kurangBayar = Math.max(0, this.grandTotal - this.paymentTotal);
            this.kembalian = Math.max(0, totalBayarRiil - this.grandTotal);
        },

        getDynamicPrice(product, qty) {
            let hargaEceran = Number(product.harga);
            let potonganTerpilih = 0;
            let grosirList = product.product_prices || [];

            if (grosirList && grosirList.length > 0) {
                let sortedGrosir = [...grosirList].sort((a, b) => Number(b.min_qty) - Number(a.min_qty));
                let match = sortedGrosir.find(g => Number(qty) >= Number(g.min_qty));
                if (match) potonganTerpilih = Number(match.potongan);
            }

            return hargaEceran - potonganTerpilih;
        },

        addToCart(product) {
            let found = this.cart.find(item => item.id === product.id);

            if (found) {
                found.qty++;
                if (!(found.is_custom_price == true || found.is_custom_price == 1)) {
                    found.harga = this.getDynamicPrice(found._originalProduct || product, found.qty);
                }
            } else {
                let isCustom = product.is_custom_price == true || product.is_custom_price == 1;
                let initialPrice = isCustom ? Number(product.harga) : this.getDynamicPrice(product, 1);
                
                this.cart.push({
                    id: product.id,
                    kode_barang: product.kode_barang,
                    nama_barang: product.nama_barang,
                    purchase_price: product.purchase_price ,
                    harga: initialPrice,
                    qty: 1,
                    is_custom_price: isCustom,
                    _originalProduct: product
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
            // Jangan overwrite harga jika produk menggunakan custom price
            if (!(item.is_custom_price == true || item.is_custom_price == 1) && item._originalProduct) {
                item.harga = this.getDynamicPrice(item._originalProduct, item.qty);
            }
            this.$nextTick(() => this.recalculate());
        },

        searchProduct() {
            let q = this.search.toLowerCase().trim();
            if (q.length < 1) {
                this.products = [];
                return;
            }
            this.products = this.allProducts.filter(product =>
                (product.nama_barang || '').toLowerCase().includes(q) ||
                (product.kode_barang || '').toLowerCase().includes(q) ||
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

        closePriceModal() {
            this.showPriceModal = false;
            this.priceSearch = '';
            this.priceResults = [];
            setTimeout(() => {
                this.$refs.barcodeInput?.focus();
            }, 50);
        },

        searchPrice() {
            let q = this.priceSearch.trim();
            if (q.length < 1) {
                this.priceResults = [];
                return;
            }
            // fetch(`/api/products/search?q=${encodeURIComponent(q)}`)
            fetch("{{ url(`/api/products/search?q=${encodeURIComponent(q)}`) }}")
                .then(r => r.json())
                .then(data => { this.priceResults = data; });
        },

        handleShortcut(e) {
            if (typeof Swal !== 'undefined' && Swal.isVisible()) return;

            if (e.key === 'F2') {
                e.preventDefault();
                this.$refs.barcodeInput?.focus();
                this.$refs.barcodeInput?.select();
            } else if (e.key === 'F3') {
                e.preventDefault();
                this.showPriceModal = true;
                setTimeout(() => { this.$refs.priceInput?.focus(); }, 50);
            } else if (e.key === 'F4') {
                e.preventDefault();
                document.getElementById('cash')?.focus();
                document.getElementById('cash')?.select();
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

        async clearCart() {
            const result = await Swal.fire({
                icon: 'warning',
                title: 'Kosongkan Cart?',
                text: 'Semua item akan dihapus',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal',
                returnFocus: false
            });

            if (result.isConfirmed) {
                this.cart = [];
                this.diskon = 0;
                this.cash = 0;
                this.voucher = 0;
                this.card = 0;
                this.hutang = 0;
                this.$nextTick(() => {
                    this.recalculate();
                    this.$refs.barcodeInput?.focus();
                });
            }
        },

        formatRupiah(value) {
            return Number(value || 0).toLocaleString('id-ID');
        },

        async saveTransaction() {
            if (this.cart.length === 0) {
                await Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Keranjang belanja kosong !', returnFocus: false });
                this.$refs.barcodeInput?.focus();
                return;
            }

            if (this.grandTotal < 0 || this.subtotal <= 0) {
                await Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Total transaksi tidak valid', returnFocus: false });
                return;
            }

            if (Number(this.hutang || 0) > 0 && !this.selectedCustomer) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Pelanggan Wajib Dipilih',
                    text: 'Transaksi mengandung hutang/kasbon. Silakan pilih pelanggan terlebih dahulu!',
                    returnFocus: false
                });
                this.$refs.customerInput?.focus();
                return;
            }

            if (this.paymentTotal < this.grandTotal) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Pembayaran Kurang',
                    text: 'Total belanja Rp ' + this.formatRupiah(this.grandTotal),
                    returnFocus: false
                });
                document.getElementById('cash')?.focus();
                return;
            }

            if (this.kembalian >= 100000) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Perhatian!',
                    text: 'Kembalian terlalu besar (Rp ' + this.formatRupiah(this.kembalian) + '), Cek ulang Diskon, Cash, Voucher, Card, dan Hutang',
                    confirmButtonText: 'Cek Ulang',
                    confirmButtonColor: '#e11d48',
                    returnFocus: false
                });
                document.getElementById('cash')?.focus();
                return;
            }

            const konfirmasiCetak = await Swal.fire({
                title: 'Cetak Nota?',
                text: 'Transaksi akan disimpan ke sistem',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya (Enter)',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#4f46e5',
                returnFocus: false
            });

            if (!konfirmasiCetak.isConfirmed) return;

            try {
                let response = await fetch( "{{ url('/kasir/store') }}" , {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        order_id: this.orderData ? this.orderData.id : null,
                        pelanggan: this.selectedCustomer ? this.selectedCustomer.id : null,
                        cart: this.cart,
                        subtotal: Number(this.subtotal || 0),
                        diskon: Number(this.diskon || 0),
                        grand_total: Number(this.grandTotal || 0),
                        cash: Number(this.cash || 0),
                        voucher: Number(this.voucher || 0),
                        card: Number(this.card || 0),
                        hutang: Number(this.hutang || 0),
                        kembalian: Number(this.kembalian || 0)
                    })
                });

                let result = await response.json();

                if (result.success) {
                    window.open('/kasir/' + result.transaction_id + '/print', '_blank');
                    window.location.href = "{{ route('kasir.index') }}"; 
                    Swal.fire({ title: 'Berhasil!', text: result.no_nota, icon: 'success', timer: 1500, showConfirmButton: false });
                    // setTimeout(() => { location.reload(); }, 1500);
                    // setTimeout(() => { 
                        
                    // }, 1500);
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

window.ALL_PRODUCTS = @json($products);
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
                    let response = await fetch( "{{ url('/api/customers' ) }}" , {
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