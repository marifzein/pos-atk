<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PembatalanOrder;
use App\Helpers\DocumentNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PesananBarangController extends Controller
{
    public function index()
    {
        // 1. Generate No Pesanan Barang Otomatis via Helper
        $nomorSO = DocumentNumber::generate('orders', 'no_pesanan', 'SO');

        // 2. PROTEKSI STRICT: Hanya load produk yang tipenya 'barang'
        $products = Product::where('type', 'barang')->where('is_active', 1)->get();
        $customers = Customer::where('status', 1)->get();

        return view('pesanan-barang.index', compact('nomorSO', 'products', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.qty' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $no_pesanan = DocumentNumber::generate('orders', 'no_pesanan', 'WO');

            $customer = null;
            if ($request->pelanggan) {
                $customer = Customer::where('kode_pelanggan', $request->pelanggan)->first();
            }

            // Simpan Data Master Order
            $order = new Order();
            $order->no_pesanan = $no_pesanan;
            $order->operator_id = Auth::id() ?? 1;
            $order->customer_id = $customer ? $customer->id : null;
            $order->customer_name_manual = $customer ? $customer->nama : 'Umum';
            $order->status = 'order'; 
            $order->catatan = $request->catatan ?? 'Pesanan Barang';
            $order->save();

            // Simpan Detail Order
            foreach ($request->cart as $item) {
                // Validasi ulang server-side: pastikan item benar-benar tipe 'barang'
                $product = Product::where('id', $item['id'])->where('type', 'barang')->first();
                if (!$product) {
                    throw new \Exception("Item " . $item['nama_barang'] . " bukan tipe Barang!");
                }

                $detail = new OrderItem();
                $detail->order_id = $order->id;
                $detail->product_id = $product->id;
                $detail->item_name = $product->name;
                $detail->qty = $item['qty'];
                $detail->purchase_price = $product->purchase_price ?? 0;
                $detail->unit_price = $item['harga'];
                $detail->subtotal = $item['qty'] * $item['harga'];
                $detail->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pesanan barang berhasil disimpan',
                'order_id' => $order->id,
                'no_nota' => $order->no_pesanan
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        $query = Order::with(['operator', 'customer', 'items']);

        if ($user && in_array($user->role, ['operator', 'kasir'])) {
            $query->where('operator_id', $user->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_pesanan', 'like', "%{$search}%")
                  ->orWhere('customer_name_manual', 'like', "%{$search}%");
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        $allowedSorts = [
            'no_pesanan' => 'no_pesanan',
            'created_at' => 'created_at',
            'status'     => 'status',
        ];

        if (array_key_exists($sortBy, $allowedSorts)) {
            $query->orderBy($allowedSorts[$sortBy], $sortDir);
        } else {
            $query->latest();
        }

        $orders = $query->paginate(10)->withQueryString();
        $customers = Customer::where('status', 1)->get();

        return view('pesanan-barang.history', compact('orders', 'customers', 'sortBy', 'sortDir'));
    }

    public function show(Order $order)
    {
        $order->load(['operator', 'customer', 'orderItems.product']);

        return view('pesanan-barang.show', compact('order'));
    }

    public function cancelOrder(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password otorisasi salah!'
            ], 422);
        }

        $order = Order::findOrFail($id);

        if (strtolower($order->status) === 'batal') {
            return response()->json([
                'success' => false,
                'message' => 'Pesanan ini sudah dibatalkan sebelumnya.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $order->status = 'batal';
            $order->save();

            PembatalanOrder::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'alasan' => $request->alasan,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'no_pesanan' => $order->no_pesanan,
                'message' => "Pembatalan no SO {$order->no_pesanan} berhasil"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembatalan: ' . $e->getMessage()
            ], 500);
        }
    }
}