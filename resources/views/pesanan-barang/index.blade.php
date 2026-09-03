@extends('layouts.app')

@section('title', 'Pesanan Barang')

@section('content')
<div class="mb-4 px-1">
    <h2 class="text-xl font-bold text-slate-800 tracking-tight flex items-center gap-2">
        <i class="ri-box-3-line text-indigo-600"></i> Pesanan ( Khusus Produk Barang )
    </h2>
</div>

<div class="w-full px-1" x-data="posBarang()">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-start w-full">

        <!-- CARD KIRI -->
        <div class="col-span-1 lg:col-span-4 xl:col-span-3 space-y-4 w-full">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-5 space-y-5">
                
                <div>
                    <label class="block text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-1.5">No. Pesanan Barang</label>
                    <input type="text" value="{{ $nomorSO }}" readonly class="w-full border border-slate-200 rounded-lg p-2.5 bg-slate-50 text-slate-700 font-bold text-sm outline-none shadow-inner">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-[11px] text-slate-400 font-bold uppercase tracking-wider">Pelanggan</label>
                        <span class="text-[10px] text-indigo-600 font-semibold hover:underline cursor-pointer">+ Tambah Baru</span>
                    </div>
                    <div class="relative" @click.outside="customerResults=[]">
                        <input x-ref="customerInput" type="text" x-model="customerSearch" @keydown.arrow-down.prevent="moveCustomerDown()" @keydown.arrow-up.prevent="moveCustomerUp()" @keydown.enter.prevent="chooseCustomer()" @input="searchCustomer()" placeholder="Cari nama / kode pelanggan..." class="w-full rounded-lg border border-slate-300 px-3 py-2.5 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-sm shadow-sm">
                        
                        <div x-show="customerResults.length" class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-xl z-50 max-h-60 overflow-y-auto">
                            <template x-for="(customer, index) in customerResults" :key="customer.kode_pelanggan">
                                <div @click="selectCustomer(customer)" :class="customerIndex === index ? 'bg-indigo-50 text-indigo-900' : ''" class="px-4 py-2.5 cursor-pointer hover:bg-slate-50 border-b border-slate-100 text-sm">
                                    <div class="font-semibold text-slate-800" x-text="customer.nama"></div>
                                    <div class="text-xs text-slate-400" x-text="customer.kode_pelanggan"></div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <template x-if="selectedCustomer">
                        <div class="mt-2.5 rounded-lg bg-indigo-50/50 border border-indigo-100 p-3 relative text-xs">
                            <div class="font-bold text-indigo-900" x-text="selectedCustomer.nama"></div>
                            <div class="text-slate-500 mt-0.5" x-text="'Telp: ' + (selectedCustomer.telepon || '-')"></div>
                            <button @click="clearCustomer()" class="mt-2 text-rose-600 font-medium hover:underline block">Hapus Pelanggan</button>
                        </div>
                    </template>
                </div>

                <!-- INFO SHORTCUT SAMAKAN DENGAN PESANAN JASA -->
                <div class="bg-amber-50/50 border border-amber-200 rounded-xl p-4">
                    <label class="block text-[11px] text-amber-800 font-bold uppercase tracking-wider mb-2 flex items-center gap-1">
                        <i class="ri-keyboard-line"></i> Shortcut Keyboard
                    </label>
                    <div class="space-y-2 text-xs text-amber-900/90">
                        <div class="flex justify-between"><span class="text-slate-500">Cari Barang</span><span class="font-mono bg-white px-1.5 py-0.5 border rounded shadow-sm text-[10px]">F2</span></div>
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

        <!-- CARD KANAN -->
        <div class="col-span-1 lg:col-span-8 xl:col-span-9 w-full">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-5 flex flex-col min-h-[560px] w-full">
                
                <div class="mb-4">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                            <i class="ri-search-2-line text-lg"></i>
                        </span>
                        <input id="barangInput" x-ref="barangInput" type="text" x-model="search" @input="searchBarang" @keydown.arrow-down.prevent="if(selectedIndex < barangItems.length - 1) selectedIndex++" @keydown.arrow-up.prevent="if(selectedIndex > 0) selectedIndex--" @keydown.enter.prevent="if(barangItems.length) addToCart(barangItems[selectedIndex])" placeholder="Scan Barcode / Kode / Nama Barang..." class="w-full border border-indigo-600 rounded-lg py-3.5 pl-11 pr-4 text-base focus:ring-2 focus:ring-indigo-100 outline-none shadow-sm font-medium placeholder-slate-400">
                        
                        <div x-show="barangItems.length" class="absolute left-0 right-0 bg-white border border-slate-200 rounded-lg shadow-2xl mt-1.5 z-50 max-h-64 overflow-y-auto">
                            <template x-for="(barang, index) in barangItems" :key="barang.id">
                                <div @click="addToCart(barang)" :class="selectedIndex === index ? 'bg-indigo-600 text-white' : 'hover:bg-slate-50'" class="p-3 border-b border-slate-100 cursor-pointer flex justify-between items-center transition-colors">
                                    <div>
                                        <div class="font-bold text-sm" :class="selectedIndex === index ? 'text-white' : 'text-slate-800'" x-text="barang.name"></div>
                                        <div class="text-xs" :class="selectedIndex === index ? 'text-indigo-200' : 'text-slate-400'" x-text="'Kode/Barcode: ' + (barang.barcode || barang.id)"></div>
                                    </div>
                                    <div class="font-extrabold text-sm" :class="selectedIndex === index ? 'text-white' : 'text-indigo-600'" x-text="formatRupiah(barang.price)"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 px-1">Keranjang Belanja</div>

                <div class="overflow-x-auto border border-slate-100 rounded-lg w-full mb-auto">
                    <table class="w-full border-collapse bg-white table-auto">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-200 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                                <th class="p-3 text-center w-12">No</th>
                                <th class="text-left p-3">Item Barang</th>
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
                                        <input type="number" min="1" x-model.number="item.qty" @change="validateQty(item)" @input="calculateItem(item)" @keydown.enter.prevent="$refs.barangInput.focus()" class="w-20 border border-slate-300 rounded-md text-center p-1.5 font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none">
                                    </td>
                                    <td class="p-3 text-right">
                                        <template x-if="item.is_custom">
                                            <div class="flex items-center justify-end gap-1">
                                                <span class="text-slate-400 text-xs font-semibold">Rp</span>
                                                <input type="number" min="0" x-model.number="item.harga" @input="validateHarga(item)" @keydown.enter.prevent="$refs.barangInput.focus()" class="w-28 border border-amber-300 bg-amber-50/30 rounded-md text-right p-1.5 font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none text-slate-800">
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

                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-slate-100 items-start w-full">
                    <div>
                        <label class="block text-[11px] text-slate-400 font-bold uppercase tracking-wider mb-1.5">Catatan Pesanan Barang</label>
                        <textarea x-model="catatan" rows="3" placeholder="Catatan pengiriman atau detail produk..." class="w-full border border-slate-300 rounded-lg p-3 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none shadow-sm placeholder-slate-400"></textarea>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="font-semibold text-slate-500 text-sm">Total Biaya Barang</span>
                            <span class="font-black text-2xl text-slate-900" x-text="formatRupiah(subtotal)"></span>
                        </div>

                        <button @click="saveTransaction()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-3.5 rounded-lg font-bold transition flex justify-center items-center shadow-md shadow-indigo-100 text-sm tracking-wide">
                            <i class="ri-save-3-line mr-2 text-lg"></i> F10 Simpan Pesanan Barang
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- MODAL CEK HARGA (F3) BARANG -->
    <div
        x-show="showPriceModal"
        x-cloak
        class="fixed inset-0 bg-black/50 flex items-center justify-center z-50"
        @keydown.escape.window="closePriceModal()"
    >
        <div class="bg-white rounded-xl p-6 w-full max-w-xl shadow-2xl">
            <div class="flex justify-between mb-4 items-center">
                <h3 class="font-bold text-xl text-slate-800">Cek Harga Barang</h3>
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
                    <div class="py-3 flex justify-between items-center">
                        <div>
                            <div class="font-semibold text-slate-800" x-text="item.name || item.nama_barang"></div>
                            <div class="text-xs text-slate-400" x-text="'Kode/Barcode: ' + (item.barcode || item.id)"></div>
                        </div>
                        <div class="text-indigo-600 font-bold" x-text="'Harga : Rp ' + Number(item.price || item.harga).toLocaleString('id-ID')"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>

<script>
function posBarang() {
    return {
        search: '',
        barangItems: [],
        cart: [],
        subtotal: 0,
        catatan: '',
        selectedIndex: 0,

        customerSearch: '',
        customerResults: [],
        selectedCustomer: null,
        customerIndex: -1,

        showPriceModal: false,
        priceSearch: '',
        priceResults: [],

        allProducts: window.ALL_BARANG_PRODUCTS || [],
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
                if (this.$refs.barangInput) this.$refs.barangInput.focus();
            });
            this.recalculate();
        },

        handleShortcut(e) {
            if (typeof Swal !== 'undefined' && Swal.isVisible()) return;

            if (e.key === 'F2') {
                e.preventDefault();
                this.$refs.barangInput?.focus();
                this.$refs.barangInput?.select();
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
                this.$refs.barangInput?.focus();
            }, 50);
        },

        searchPrice() {
            let q = this.priceSearch.toLowerCase().trim();
            if (q.length < 1) {
                this.priceResults = [];
                return;
            }
            this.priceResults = this.allProducts.filter(item => 
                item.type === 'barang' && (
                    (item.name || '').toLowerCase().includes(q) ||
                    (item.barcode || '').toLowerCase().includes(q)
                )
            ).slice(0, 10);
        },

        async clearCart() {
            const result = await Swal.fire({
                icon: 'warning',
                title: 'Kosongkan Cart?',
                text: 'Semua item pesanan barang akan dihapus',
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
                    this.$refs.barangInput?.focus();
                });
            }
        },

        searchBarang() {
            let q = this.search.toLowerCase().trim();
            if (q.length < 1) {
                this.barangItems = [];
                return;
            }
            this.barangItems = this.allProducts.filter(item => 
                item.type === 'barang' && (
                    (item.name || '').toLowerCase().includes(q) ||
                    (item.barcode || '').toLowerCase().includes(q)
                )
            ).slice(0, 10);
            this.selectedIndex = 0;
        },

        addToCart(barang) {
            let found = this.cart.find(item => item.id === barang.id);
            if (found) {
                found.qty++;
            } else {
                this.cart.push({
                    id: barang.id,
                    nama_barang: barang.name,
                    harga: Number(barang.price),
                    qty: 1,
                    is_custom: Boolean(barang.is_custom_price)
                });
            }
            this.search = '';
            this.barangItems = [];
            this.selectedIndex = 0;
            this.recalculate();
            this.$nextTick(() => {
                if (this.$refs.barangInput) this.$refs.barangInput.focus();
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
                if (this.$refs.barangInput) this.$refs.barangInput.focus();
            });
        },
        clearCustomer() {
            this.selectedCustomer = null;
            this.customerSearch = '';
            this.$nextTick(() => {
                if (this.$refs.barangInput) this.$refs.barangInput.focus();
            });
        },

        async saveTransaction() {
            if (this.cart.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Keranjang Kosong', text: 'Pilih minimal satu barang terlebih dahulu!' });
                return;
            }

            const confirmSubmit = await Swal.fire({
                title: 'Simpan Pesanan Barang?',
                text: "Pesanan ini akan disimpan ke sistem.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan',
                cancelButtonColor: '#6366f1'
            });

            if (!confirmSubmit.isConfirmed) return;

            let response = await fetch( "{{ url('/api/pesanan-barang' ) }}", {
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

window.ALL_BARANG_PRODUCTS = @json($products);
window.ALL_CUSTOMERS = @json($customers);
</script>
@endsection