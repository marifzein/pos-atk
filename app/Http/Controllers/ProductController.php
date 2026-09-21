<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\DocumentNumber;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $branches = Branch::where('is_active', 1)->get();

        // Query utama berbasis Product
        $query = Product::with(['supplier', 'stocks.branch']);

        // 1. Handling Filter Cabang & Hak Akses Role
        $selectedBranchId = $request->branch_id;

        if (!in_array(strtolower($user->role), ['owner', 'admin', 'developer']) && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
        }

        // Eager load relasi stocks untuk tampilan baris tabel
        $query->with(['stocks' => function ($q) use ($selectedBranchId) {
            $q->with('branch');
            if ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId);
            }
        }]);

        // Filter ketersediaan berdasarkan Cabang yang dipilih
        if ($selectedBranchId) {
            $query->where(function ($q) use ($selectedBranchId) {
                $q->whereHas('stocks', function ($q2) use ($selectedBranchId) {
                    $q2->where('branch_id', $selectedBranchId);
                });

                // Hanya sertakan jasa jika filter type TIDAK sedang memfilter 'barang'
                if (!request()->filled('type') || request('type') === 'jasa') {
                    $q->orWhere('type', 'jasa');
                }
            });
        }

        // 2. Filter Pencarian Teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                ->orWhere('barcode', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // 3. Filter Tipe (Barang / Jasa) - INI YANG TADI DIBUTUHKAN
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // 4. Filter Stok
        if ($request->filled('stock')) {
            $stockStatus = $request->stock;

            $query->where(function ($q) use ($stockStatus, $selectedBranchId) {
                $q->where('type', 'barang')->whereHas('stocks', function ($q2) use ($stockStatus, $selectedBranchId) {
                    if ($selectedBranchId) {
                        $q2->where('branch_id', $selectedBranchId);
                    }

                    if ($stockStatus === 'available') {
                        $q2->whereColumn('stock', '>', 'min_stock');
                    } elseif ($stockStatus === 'low') {
                        $q2->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock');
                    } elseif ($stockStatus === 'empty') {
                        $q2->where('stock', '<=', 0);
                    }
                });
            });
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('products.index', compact('products', 'branches', 'selectedBranchId', 'user'));
    }

    // pencarian ploduk2 
    public function search(Request $request)
    {
        $q = trim($request->q);

        if (!$q) {
            return response()->json([]);
        }

        $products = Product::query()
        ->where(function ($query) use ($q) {
            $query->where('products.name', 'like', "%{$q}%")
                  ->orWhere('products.sku', 'like', "%{$q}%")
                  ->orWhere('products.barcode', 'like', "%{$q}%");
        })
        ->where('products.is_active', 1) // Filter hanya produk aktif
        ->limit(10)
        ->get([
            'products.id',
            'products.sku',
            'products.barcode',
            'products.name',
            'products.price',
            'products.satuan',
            'products.type',
            'products.stock'
            ]);

        return response()->json($products);
    }

    public function search_barang(Request $request)
    {
        $q = trim($request->q);

        if (!$q) {
            return response()->json([]);
        }

        $products = Product::query()
        ->where(function ($query) use ($q) {
            $query->where('products.name', 'like', "%{$q}%")
                  ->orWhere('products.sku', 'like', "%{$q}%")
                  ->orWhere('products.barcode', 'like', "%{$q}%");
        })
        ->where('products.type', 'barang')    // Filter hanya tipe barang
        ->where('products.is_active', 1) // Filter hanya produk aktif
        ->limit(10)
        ->get([
            'products.id',
            'products.sku',
            'products.barcode',
            'products.name',
            'products.purchase_price',
            'products.price',
            'products.satuan',
            'products.type',
            'products.stock'
            ]);

        return response()->json($products);
    }

    public function search_jasa(Request $request)
    {
        $q = trim($request->q);

        if (!$q) {
            return response()->json([]);
        }

        $products = Product::query()
        ->where(function ($query) use ($q) {
            $query->where('products.name', 'like', "%{$q}%")
                  ->orWhere('products.sku', 'like', "%{$q}%")
                  ->orWhere('products.barcode', 'like', "%{$q}%");
        })
        ->where('products.type', 'jasa')    // Filter hanya tipe barang
        ->where('products.is_active', 1) // Filter hanya produk aktif
        ->limit(10)
        ->get([
            'products.id',
            'products.sku',
            'products.barcode',
            'products.name',
            'products.purchase_price',
            'products.price',
            'products.satuan',
            'products.type',
            'products.stock'
            ]);

        return response()->json($products);
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', 1)->orderBy('name', 'asc')->get();
        // Ambil daftar cabang aktif
        $branches = Branch::where('is_active', 1)->get();
        return view('products.create', compact('suppliers', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'sku' => 'nullable|string|max:255|unique:products,sku',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'type' => 'required|in:barang,jasa',
            'is_custom_price' => 'nullable|boolean',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'satuan' => 'required|string|max:255',
            'purchase_price' => 'required|integer|min:0',
            'price' => 'required|integer|min:0',
            'catatan' => 'nullable|string',
            // Validasi array cabang (hanya wajib jika tipe barang)
            'branches' => 'nullable|array',
            'branches.*.stock' => 'nullable|integer|min:0',
            'branches.*.min_stock' => 'nullable|integer|min:0',
        ]);

        $sku = $request->sku;
        if (!$sku) {
            $sku = DocumentNumber::generateMaster('products', 'sku', 'BRG', 4);
        }

        $isCustomPrice = ($request->type === 'jasa' && $request->has('is_custom_price')) ? 1 : 0;

        DB::transaction(function () use ($request, $sku, $isCustomPrice) {
            // 1. Simpan Master Produk
            $product = Product::create([
                'barcode' => $request->barcode,
                'sku' => $sku,
                'name' => $request->name,
                'type' => $request->type,
                'is_custom_price' => $isCustomPrice,
                'supplier_id' => $request->supplier_id,
                'satuan' => $request->satuan,
                'purchase_price' => $request->purchase_price,
                'price' => $request->price,
                'stock' => 0, // Dikosongkan karena stok pindah ke product_stocks
                'min_stock' => 0,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'catatan' => $request->catatan,
            ]);

            // 2. Simpan Stok & Stock Movement per Cabang jika tipe 'barang'
            // 2. Simpan Stok & Stock Movement per Cabang jika tipe 'barang'
            if ($product->type === 'barang' && $request->has('branches')) {
                foreach ($request->branches as $branchData) {
                    // Gunakan nama variabel $targetBranchId agar tidak menimpa konteks loop
                    $targetBranchId = $branchData['branch_id'] ?? null;
                    $stockQty       = (int) ($branchData['stock'] ?? 0);
                    $minStockQty    = (int) ($branchData['min_stock'] ?? 0);

                    if (!$targetBranchId) {
                        continue;
                    }

                    // Insert ke product_stocks
                    ProductStock::create([
                        'product_id' => $product->id,
                        'branch_id'  => $targetBranchId,
                        'stock'      => $stockQty,
                        'min_stock'  => $minStockQty,
                    ]);

                    // Insert ke stock_movements jika stok > 0
                    if ($stockQty > 0) {
                        StockMovement::create([
                            'branch_id'    => $targetBranchId,
                            'product_id'   => $product->id,
                            'type'         => 'OPENING',
                            'qty'          => $stockQty,
                            'stock_before' => 0,
                            'stock_after'  => $stockQty,
                            'reference_no' => 'OPENING',
                            'notes'        => 'Stok awal produk',
                        ]);
                    }
                }
            }
        });

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $suppliers = Supplier::where('is_active', 1)->orderBy('name', 'asc')->get();
        // Load cabang beserta stok khusus produk ini
        $branches = Branch::where('is_active', 1)->get();
        
        // Map product_stocks ke key branch_id agar mudah diakses di Blade
        $productStocks = ProductStock::where('product_id', $product->id)
            ->get()
            ->keyBy('branch_id');

        return view('products.edit', compact('product', 'suppliers', 'branches', 'productStocks'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'type' => 'required|in:barang,jasa',
            'is_custom_price' => 'nullable|boolean',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'satuan' => 'required|string|max:255',
            'purchase_price' => 'required|integer|min:0',
            'price' => 'required|integer|min:0',
            'catatan' => 'nullable|string',
            'branches' => 'nullable|array',
            'branches.*.min_stock' => 'nullable|integer|min:0',
        ]);

        $isCustomPrice = ($request->type === 'jasa' && $request->has('is_custom_price')) ? 1 : 0;

        DB::transaction(function () use ($request, $product, $isCustomPrice) {
            // 1. Update Master Produk
            $product->update([
                'barcode' => $request->barcode,
                'name' => $request->name,
                'brand' => $request->brand,
                'type' => $request->type,
                'is_custom_price' => $isCustomPrice,
                'supplier_id' => $request->supplier_id,
                'satuan' => $request->satuan,
                'purchase_price' => $request->purchase_price,
                'price' => $request->price,
                'is_active' => $request->has('is_active') ? 1 : 0,
                'catatan' => $request->catatan,
            ]);

            // 2. Update Minimal Stok per Cabang
            // Catatan: Jumlah stok aktual TIDAK DIEDIT di sini, melainkan via Penerimaan/Stok Opname
            if ($product->type === 'barang' && $request->has('branches')) {
                foreach ($request->branches as $branchId => $branchData) {
                    ProductStock::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'branch_id'  => $branchId,
                        ],
                        [
                            'min_stock' => (int) ($branchData['min_stock'] ?? 0),
                        ]
                    );
                }
            }
        });

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }
}