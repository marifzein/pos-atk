<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Helpers\DocumentNumber;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('supplier');

        if ($request->has('search') && $request->search != '') {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('barcode', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%');
        }

        $products = $query->latest()->paginate(10)->withQueryString();

        return view('products.index', compact('products'));
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


    public function create()
    {
        $suppliers = Supplier::where('is_active', 1)->orderBy('name', 'asc')->get();
        return view('products.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'nullable|string|max:255|unique:products,barcode',
            'sku' => 'nullable|string|max:255|unique:products,sku', // <-- Validasi unik SKU
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'type' => 'required|in:barang,jasa',
            'is_custom_price'  => 'nullable|boolean',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'satuan' => 'required|string|max:255',
            'purchase_price' => 'required|integer|min:0',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'catatan' => 'nullable|string',
        ]);

        // Logic Hybrid SKU otomatis/manual bos[cite: 11, 12]
        $sku = $request->sku;
        if (!$sku) {
            // Pakai method generateMaster (table, field, prefix, digit)[cite: 12]
            $sku = DocumentNumber::generateMaster('products', 'sku', 'BRG', 4); // Hasil: BRG0001
        }

        // is_custom_price HANYA bisa bernilai 1 jika tipe-nya 'jasa'
        $isCustomPrice = ($request->type === 'jasa' && $request->has('is_custom_price')) ? 1 : 0;


        Product::create([
            'barcode' => $request->barcode,
            'sku' => $sku,
            'name' => $request->name,
            'brand' => $request->brand,
            'type' => $request->type,
            'is_custom_price' => $isCustomPrice,
            'supplier_id' => $request->supplier_id,
            'satuan' => $request->satuan,
            'purchase_price' => $request->purchase_price,
            'price' => $request->price,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $suppliers = Supplier::where('is_active', 1)->orderBy('name', 'asc')->get();
        return view('products.edit', compact('product', 'suppliers'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'barcode' => 'nullable|string|max:255|unique:products,barcode,' . $product->id,
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'type' => 'required|in:barang,jasa',
            'is_custom_price'  => 'nullable|boolean',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'satuan' => 'required|string|max:255',
            'purchase_price' => 'required|integer|min:0',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'catatan' => 'nullable|string',
        ]);

        // Guard: Jika diganti ke 'barang', paksa is_custom_price jadi 0
        $isCustomPrice = ($request->type === 'jasa' && $request->has('is_custom_price')) ? 1 : 0;
        
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
            'stock' => $request->stock,
            'min_stock' => $request->min_stock,
            'is_active' => $request->has('is_active') ? 1 : 0,
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }
}