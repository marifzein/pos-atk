<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StockCardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $branches = Branch::where('is_active', 1)->get();

        // 1. Cek Hak Akses Role (Hanya Owner & Admin yang bebas pilih cabang)
        $isGlobalUser = in_array(strtolower($user->role), ['owner', 'admin']);
        
        $selectedBranchId = $request->branch_id;
        if (!$isGlobalUser && $user->branch_id) {
            // Jika bukan owner/admin, paksa ke branch user
            $selectedBranchId = $user->branch_id;
        }

        // 2. Query khusus Produk tipe 'barang'
        $query = Product::where('type', 'barang')
            ->with(['supplier']);

        // Eager load relasi stocks terfilter cabang
        $query->with(['stocks' => function ($q) use ($selectedBranchId) {
            $q->with('branch');
            if ($selectedBranchId) {
                $q->where('branch_id', $selectedBranchId);
            }
        }]);

        if ($selectedBranchId) {
            $query->whereHas('stocks', function ($q2) use ($selectedBranchId) {
                $q2->where('branch_id', $selectedBranchId);
            });
        }

        // 3. Filter Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // 4. Filter Stok Status
        if ($request->filled('stock')) {
            $stockStatus = $request->stock;

            $query->whereHas('stocks', function ($q2) use ($stockStatus, $selectedBranchId) {
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
        }

        $products = $query->latest()->paginate(15)->withQueryString();

        return view('stock-cards.index', compact('products', 'branches', 'selectedBranchId', 'isGlobalUser'));
    }

    public function show(Request $request, Product $product)
    {
        $user = Auth::user();
        $isGlobalUser = in_array(strtolower($user->role), ['owner', 'admin']);

        // Tangkap branch_id dari query string URL (?branch_id=X)
        $branchId = $request->query('branch_id');

        // Jika bukan owner/admin, pastikan branch_id dikunci ke cabang user
        if (!$isGlobalUser && $user->branch_id) {
            $branchId = $user->branch_id;
        }

        // 1. Ambil data record `product_stocks` sesuai product_id & branch_id
        $productStock = null;
        if ($branchId) {
            $productStock = ProductStock::with('branch')
                ->where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->first();
        }

        // 2. Query riwayat mutasi stok (stock_movements) terfilter cabang
        $movementsQuery = $product->stockMovements()->with('branch');
        if ($branchId) {
            $movementsQuery->where('branch_id', $branchId);
        }

        $movements = $movementsQuery->latest()->get();

        return view('stock-cards.show', compact('product', 'productStock', 'movements', 'branchId'));
    }
}