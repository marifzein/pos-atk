@extends('layouts.mobile-app')

@section('title', 'Pesanan Jasa')
@section('page_subtitle', 'WO: ' . $nomorWO)

@section('content')
<div x-data="posJasaMobile()">

    <!-- SCROLLABLE BODY AREA -->
    <div class="flex-1 space-y-3.5 pb-48">
        
        <!-- 1. INPUT PENCARIAN / SCAN JASA + TOMBOL CEK HARGA -->
        <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-100">
            <div class="flex items-center gap-2">
                <div class="relative flex-1" @click.outside="jasaItems = []">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                        <i class="ri-search-2-line text-slate-400 text-xl"></i>
                    </span>
                    <input
                        id="jasaInputMobile"
                        x-ref="jasaInputMobile"
                        type="text"
                        x-model="search"
                        @input="searchJasa"
                        @keydown.arrow-down.prevent="if(selectedIndex < jasaItems.length - 1) selectedIndex++"
                        @keydown.arrow-up.prevent="if(selectedIndex > 0) selectedIndex--"
                        @keydown.enter.prevent="if(jasaItems.length) addToCart(jasaItems[selectedIndex])"
                        placeholder="Cari kode / nama jasa..."
                        class="w-full bg-slate-50 border-0 focus:ring-2 focus:ring-indigo-500 rounded-xl pl-11 pr-4 py-3 text-sm font-bold outline-none transition placeholder:text-slate-400"
                    >

                    <!-- Dropdown Hasil Pencarian Jasa -->
                    <div x-show="jasaItems.length" class="absolute left-0 right-0 bg-white border border-slate-200 rounded-xl shadow-xl mt-1.5 z-40 max-h-60 overflow-y-auto divide-y divide-slate-100" x-cloak>
                        <template x-for="(jasa, index) in jasaItems" :key="jasa.id">
                            <div
                                @click="addToCart(jasa)"
                                :class="selectedIndex === index ? 'bg-indigo-50 border-l-4 border-indigo-600' : ''"
                                class="p-3 cursor-pointer flex justify-between items-center" 
                            >
                                <div>
                                    <div class="font-bold text-slate-800 text-sm" x-text="jasa.name"></div>
                                    <div class="text-xs font-mono text-slate-400" x-text="'Kode: ' + (jasa.barcode || jasa.id)"></div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-black text-indigo-600" x-text="'Rp ' + formatRupiah(jasa.price)"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Tombol Cek Harga Modal -->
                <button @click="openPriceModal()" type="button" title="Cek Harga Jasa" class="w-12 h-12 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-xl transition flex items-center justify-center active:scale-95 flex-shrink-0">
                    <i class="ri-price-tag-3-line text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- 2. PILIH DATA PELANGGAN -->
        <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-100">
            <div class="flex justify-between items-center mb-2">
                <label class="text-xs font-bold uppercase tracking-wider text-slate-500 flex items-center gap-1">
                    <i class="ri-user-smile-line text-indigo-500 text-sm"></i> Pelanggan
                </label>
                <button type="button" @click="$dispatch('open-customer-modal')" class="text-xs text-indigo-600 font-bold hover:underline">+ Baru</button>
            </div>
            
            <div class="relative" @click.outside="customerResults=[]">
                <input
                    x-ref="customerInput"
                    type="text"
                    x-model="customerSearch"
                    @keydown.arrow-down.prevent="moveCustomerDown()"
                    @keydown.arrow-up.prevent="moveCustomerUp()"
                    @keydown.enter.prevent="chooseCustomer()"
                    @input="searchCustomer()"
                    placeholder="Cari nama pelanggan..."
                    class="w-full bg-slate-50 border-0 focus:ring-2 focus:ring-indigo-500 text-sm font-semibold rounded-xl px-3.5 py-2.5 outline-none transition placeholder:text-slate-400"
                >

                <div x-show="customerResults.length" class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg z-40 max-h-48 overflow-y-auto divide-y divide-slate-100" x-cloak>
                    <template x-for="(customer, index) in customerResults" :key="customer.kode_pelanggan">
                        <div @click="selectCustomer(customer)" :class="customerIndex===index ? 'bg-indigo-50' : ''" class="px-3.5 py-2.5 cursor-pointer text-sm">
                            <div class="font-bold text-slate-800" x-text="customer.nama"></div>
                            <div class="text-xs text-slate-400" x-text="'Telp: ' + (customer.telepon || '-')"></div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Detail Pelanggan Terpilih -->
            <template x-if="selectedCustomer">
                <div class="mt-2.5 rounded-xl bg-indigo-50 border border-indigo-100 p-2.5 flex items-center justify-between">
                    <div class="min-w-0">
                        <div class="font-bold text-indigo-950 text-sm" x-text="selectedCustomer.nama"></div>
                        <div class="text-xs text-indigo-700 truncate" x-text="'Telp: ' + (selectedCustomer.telepon || '-')"></div>
                    </div>
                    <button @click="clearCustomer()" class="text-xs text-red-500 font-bold bg-white shadow-3xs rounded-md px-2.5 py-1 flex-shrink-0 active:scale-95 transition">Lepas</button>
                </div>
            </template>
        </div>

        <!-- 3. KERANJANG ITEM JASA -->
        <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-100">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-black text-slate-800 text-sm uppercase tracking-wider flex items-center gap-1.5">
                    <i class="ri-shopping-cart-2-line text-indigo-600 text-base"></i> Item Jasa 
                    <span class="text-xs px-2 py-0.5 bg-indigo-50 text-indigo-600 rounded-full font-extrabold" x-text="cart.length"></span>
                </h3>
                <button x-show="cart.length > 0" @click="clearCart()" type="button" class="text-xs text-red-500 font-bold">Kosongkan</button>
            </div>

            <div class="space-y-2.5">
                <template x-for="(item, index) in cart" :key="item.id">
                    <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3 flex flex-col gap-2 shadow-2xs">
                        
                        <div class="flex justify-between items-start gap-2">
                            <div class="min-w-0">
                                <div class="font-bold text-slate-800 text-sm tracking-tight break-words flex items-center gap-1">
                                    <span x-text="item.nama_barang"></span>
                                    <template x-if="item.is_custom">
                                        <span class="text-[9px] bg-amber-50 text-amber-700 border border-amber-200 px-1 py-0.2 rounded font-medium">Custom</span>
                                    </template>
                                </div>
                                <div class="text-xs font-bold text-slate-400 mt-0.5" x-text="'Tarif: @Rp ' + formatRupiah(item.harga)"></div>
                            </div>
                            <button @click="removeItem(item.id)" class="text-red-400 p-1.5 active:scale-90 transition rounded-lg hover:bg-red-50">
                                <i class="ri-delete-bin-6-line text-lg"></i>
                            </button>
                        </div>

                        <!-- Jika Item Custom, Edit Harga -->
                        <template x-if="item.is_custom">
                            <div class="bg-amber-50/60 p-2 rounded-lg border border-amber-200/60 flex items-center justify-between gap-2">
                                <span class="text-xs text-amber-900 font-bold">Harga Custom:</span>
                                <div class="flex items-center gap-1">
                                    <span class="text-xs text-slate-400 font-bold">Rp</span>
                                    <input type="number" min="0" x-model.number="item.harga" @input="recalculate()" class="w-28 bg-white border border-amber-300 rounded px-2 py-1 text-right font-bold text-xs outline-none">
                                </div>
                            </div>
                        </template>

                        <div class="flex justify-between items-center pt-2 border-t border-slate-200/60">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 block uppercase">Subtotal</span>
                                <span class="text-sm font-black text-indigo-600" x-text="'Rp ' + formatRupiah(item.qty * item.harga)"></span>
                            </div>

                            <!-- Counter Qty Mobile -->
                            <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-3xs">
                                <button type="button" @click="if(item.qty > 1) { item.qty--; recalculate(); }" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold active:bg-slate-200">
                                    <i class="ri-subtract-line text-sm"></i>
                                </button>
                                <input
                                    type="number"
                                    min="1"
                                    x-model.number="item.qty"
                                    @change="validateQty(item)"
                                    @input="recalculate()"
                                    class="w-10 border-0 text-center font-black text-sm text-slate-800 p-0 focus:ring-0"
                                />
                                <button type="button" @click="item.qty++; recalculate();" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center font-bold active:bg-slate-200">
                                    <i class="ri-add-line text-sm"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </template>

                <div x-show="cart.length === 0" class="text-center py-8 text-slate-400 border border-dashed border-slate-200 rounded-xl bg-slate-50/50 text-sm">
                    Belum ada item jasa dipilih.
                </div>
            </div>
        </div>

        <!-- 4. CATATAN PESANAN / PENGERJAAN -->
        <div class="bg-white rounded-2xl p-3.5 shadow-xs border border-slate-100">
            <label class="text-xs font-bold uppercase tracking-wider text-slate-500 block mb-1">Catatan Pengerjaan / Desain</label>
            <textarea x-model="catatan" rows="2" placeholder="Misal: 'desain banner 3x5, finishing mata ayam'..." class="w-full bg-slate-50 border-0 focus:ring-2 focus:ring-indigo-500 text-sm rounded-xl p-3 outline-none"></textarea>
        </div>

    </div>

    <!-- ========================================================== -->
    <!-- STICKY FOOTER DI BAWAH HP (TOTAL BIAYA & TOMBOL SIMPAN)     -->
    <!-- ========================================================== -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 shadow-2xl p-4 z-40 rounded-t-2xl">
        <div class="max-w-md mx-auto space-y-2">
            
            <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide block">Total Biaya Jasa</span>
                    <span class="text-xl font-black text-slate-900 tracking-tight block truncate" x-text="'Rp ' + formatRupiah(subtotal)"></span>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-indigo-600 block bg-indigo-50 px-2 py-1 rounded-lg">WO JASA</span>
                </div>
            </div>

            <!-- Tombol Simpan -->
            <button type="button" @click="saveTransaction()" class="w-full bg-indigo-600 active:bg-indigo-700 text-white font-black text-sm py-3.5 px-4 rounded-xl flex items-center justify-center gap-2 shadow-lg active:scale-95 transition-all">
                <i class="ri-save-3-line text-lg"></i>
                <span>SIMPAN PESANAN JASA</span>
            </button>
        </div>
    </div>

    <!-- MODAL CEK HARGA JASA -->
    <div x-show="showPriceModal" x-cloak class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 z-50" @keydown.escape.window="closePriceModal()">
        <div class="bg-white rounded-2xl p-4 w-full max-w-sm shadow-2xl" @click.outside="closePriceModal()">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-black text-slate-800 text-base flex items-center gap-1.5">
                    <i class="ri-price-tag-2-line text-indigo-600"></i> Cek Tarif Jasa
                </h3>
                <button type="button" @click="closePriceModal()" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center font-bold">✕</button>
            </div>

            <input
                x-ref="priceInput"
                x-model="priceSearch"
                @input="searchPrice()"
                placeholder="Ketik nama / kode jasa..."
                class="w-full bg-slate-50 border border-slate-200 focus:ring-2 focus:ring-indigo-500 rounded-xl p-3 text-sm font-semibold outline-none shadow-3xs"
            >

            <div class="mt-3 max-h-56 overflow-y-auto divide-y divide-slate-100 rounded-xl border border-slate-100">
                <template x-for="item in priceResults" :key="item.id">
                    <div class="p-3 bg-slate-50/50 flex justify-between items-center">
                        <div class="font-bold text-slate-800 text-sm" x-text="item.name"></div>
                        <div class="text-sm font-black text-indigo-600" x-text="'Rp ' + formatRupiah(item.price)"></div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- MODAL PELANGGAN BARU -->
    <div
        x-data="customerModal"
        @open-customer-modal.window="showCustomerModal = true; $nextTick(() => $refs.newCustomerName.focus())"
        x-show="showCustomerModal"
        x-cloak
        class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 z-50"
        @keydown.escape.window="showCustomerModal = false;"
    >
        <div class="bg-white rounded-2xl p-4 w-full max-w-sm shadow-2xl" @click.outside="showCustomerModal = false">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-black text-sm text-slate-800 flex items-center gap-1">
                    <i class="ri-user-add-line text-indigo-600"></i> Pelanggan Baru
                </h3>
                <button type="button" @click="showCustomerModal = false" class="w-7 h-7 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center">✕</button>
            </div>

            <div class="space-y-2.5">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-0.5">Nama Lengkap</label>
                    <input x-ref="newCustomerName" type="text" x-model="newCustomer.nama" @keydown.enter.prevent="$refs.newCustomerPhone.focus()" class="w-full bg-slate-50 border-0 focus:ring-2 focus:ring-indigo-500 rounded-xl p-2.5 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-0.5">No. HP</label>
                    <input x-ref="newCustomerPhone" type="text" x-model="newCustomer.telepon" @keydown.enter.prevent="$refs.newCustomerAlamat.focus()" class="w-full bg-slate-50 border-0 focus:ring-2 focus:ring-indigo-500 rounded-xl p-2.5 text-sm outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase tracking-wide mb-0.5">Alamat</label>
                    <textarea x-ref="newCustomerAlamat" x-model="newCustomer.alamat" rows="2" class="w-full bg-slate-50 border-0 focus:ring-2 focus:ring-indigo-500 rounded-xl p-2.5 text-sm outline-none"></textarea>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" @click="showCustomerModal = false" class="px-3.5 py-2 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold">Batal</button>
                <button type="button" @click="saveNewCustomer()" class="px-3.5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold">Simpan</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function posJasaMobile() {
    return {
        search: '',
        jasaItems: [],
        cart: [],
        subtotal: 0,
        catatan: '',
        selectedIndex: 0,

        customerSearch: '',
        customerResults: [],
        customerIndex: -1,
        selectedCustomer: null,

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
            this.recalculate();
        },

        openPriceModal() {
            this.showPriceModal = true;
            this.priceSearch = '';
            this.priceResults = [];
            this.$nextTick(() => { this.$refs.priceInput?.focus(); });
        },

        closePriceModal() {
            this.showPriceModal = false;
        },

        searchPrice() {
            let q = this.priceSearch.toLowerCase().trim();
            if (q.length < 1) { this.priceResults = []; return; }
            this.priceResults = this.allProducts.filter(item => 
                item.type === 'jasa' && (
                    (item.name || '').toLowerCase().includes(q) ||
                    (item.barcode || '').toLowerCase().includes(q)
                )
            ).slice(0, 10);
        },

        searchJasa() {
            let q = this.search.toLowerCase().trim();
            if (q.length < 1) { this.jasaItems = []; return; }
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
            this.recalculate();
        },

        removeItem(id) {
            this.cart = this.cart.filter(item => item.id !== id);
            this.recalculate();
        },

        validateQty(item) {
            item.qty = parseInt(item.qty);
            if (isNaN(item.qty) || item.qty < 1) item.qty = 1;
        },

        recalculate() {
            this.subtotal = this.cart.reduce((total, item) => total + (Number(item.qty) * Number(item.harga)), 0);
        },

        formatRupiah(val) {
            return Number(val || 0).toLocaleString('id-ID');
        },

        searchCustomer() {
            let keyword = this.customerSearch.toLowerCase().trim();
            if (keyword.length < 2) { this.customerResults = []; return; }
            this.customerResults = this.allCustomers.filter(c => 
                c.nama.toLowerCase().includes(keyword) || 
                (c.kode_pelanggan || '').toLowerCase().includes(keyword)
            ).slice(0, 5);
        },

        selectCustomer(customer) {
            this.selectedCustomer = customer;
            this.customerSearch = customer.nama;
            this.customerResults = [];
        },

        clearCustomer() {
            this.selectedCustomer = null;
            this.customerSearch = '';
        },

        async clearCart() {
            const result = await Swal.fire({ text: 'Kosongkan keranjang?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya' });
            if (result.isConfirmed) {
                this.cart = [];
                this.catatan = '';
                this.recalculate();
            }
        },

        async saveTransaction() {
            if (this.cart.length === 0) {
                Swal.fire({ icon: 'warning', text: 'Pilih minimal satu layanan jasa!' });
                return;
            }

            const confirmSubmit = await Swal.fire({
                title: 'Simpan Pesanan Jasa?',
                text: "Pesanan ini akan disimpan ke sistem.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Simpan'
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

document.addEventListener('alpine:init', () => {
    Alpine.data('customerModal', () => ({
        showCustomerModal: false,
        newCustomer: { nama: '', telepon: '', alamat: '' },

        async saveNewCustomer() {
            if (!this.newCustomer.nama.trim()) {
                Swal.fire({ icon: 'warning', text: 'Nama pelanggan wajib diisi!' });
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
                    Swal.fire({ icon: 'success', text: 'Pelanggan berhasil disimpan', timer: 1200, showConfirmButton: false });
                } else {
                    Swal.fire('Error', result.message || 'Gagal menyimpan pelanggan', 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Terjadi kesalahan sistem', 'error');
            }
        }
    }));
});

window.ALL_JASA_PRODUCTS = @json($products);
window.ALL_CUSTOMERS = @json($customers);
</script>
@endpush