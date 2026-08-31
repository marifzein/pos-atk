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

class PesananJasaController extends Controller
{
    public function index()
    {
       // 1. Generate No Pesanan Jasa Otomatis via Helper
        $nomorWO = DocumentNumber::generate('orders', 'no_pesanan', 'WO');

        // 2. PROTEKSI STRICT: Hanya load produk yang tipenya 'jasa'
        $products = Product::where('type', 'jasa')->where('is_active', 1)->get();
        $customers = Customer::where('status', 1)->get();

        return view('pesanan-jasa.index', compact('nomorWO', 'products', 'customers'));
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
            // Re-generate No Pesanan untuk menghindari race condition
            // $dateCode = now()->format('Ymd');
            // $lastOrder = Order::where('no_pesanan', 'like', "JS-{$dateCode}-%")->latest()->first();
            // $nextNumber = $lastOrder ? str_pad(intval(substr($lastOrder->no_pesanan, -5)) + 1, 5, '0', STR_PAD_LEFT) : '00001';
            // $noNota = "JS-{$dateCode}-{$nextNumber}";

            $no_pesanan = DocumentNumber::generate('orders', 'no_pesanan', 'WO');

            // Cari customer berdasarkan kode_pelanggan jika dikirim
            $customer = null;
            if ($request->pelanggan) {
                $customer = Customer::where('kode_pelanggan', $request->pelanggan)->first();
            }

            // Simpan Data Master Order
            $order = new Order();
            $order->no_pesanan = $no_pesanan;
            $order->operator_id = Auth::id() ?? 1; // Fallback ke ID 1 jika auth belum diset
            $order->customer_id = $customer ? $customer->id : null;
            $order->customer_name_manual = $customer ? $customer->nama : 'Umum';
            $order->status = 'order'; 
            $order->catatan = $request->catatan ?? 'Pesanan Jasa';
            $order->save();

            // Simpan Detail Order (order_items)
            foreach ($request->cart as $item) {
                // Validasi ulang di sisi server untuk memastikan item beneran 'jasa'
                $product = Product::where('id', $item['id'])->where('type', 'jasa')->first();
                if (!$product) {
                    throw new \Exception("Item " . $item['nama_barang'] . " bkn tipe Jasa!");
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
                'message' => 'Pesanan jasa berhasil disimpan',
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

        // 1. Inisialisasi Query Master Order
        $query = Order::with(['operator', 'customer', 'items']);

        // 2. Filter Berdasarkan Role: Operator hanya bisa lihat transaksinya sendiri
        if ($user && in_array($user->role, ['operator', 'kasir'])) {
            $query->where('operator_id', $user->id);
        }

        // 3. Filter Pencarian No Pesanan / Kode Pelanggan
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_pesanan', 'like', "%{$search}%")
                  ->orWhere('customer_name_manual', 'like', "%{$search}%");
            });
        }

        // 4. Filter Nama Pelanggan Spesifik
        // if ($request->filled('customer_id')) {
        //     $query->where('customer_id', $request->customer_id);
        // }

        // 5. Sorting Dinamis
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');

        // Whitelist kolom agar aman dari SQL Injection
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

        return view('pesanan-jasa.history', compact('orders', 'customers', 'sortBy', 'sortDir'));
    }

    public function show(Order $order)
    {
        $order->load(['operator', 'customer', 'orderItems.product']);

        return view('pesanan-jasa.show', compact('order'));
    }

    // Method pembatalan pesanan jasa
    public function cancelOrder(Request $request, $id)
    {
        $request->validate([
            'alasan' => 'required|string|max:500',
            'password' => 'required|string',
        ], [
            'alasan.required' => 'Alasan pembatalan wajib diisi.',
            'password.required' => 'Password otorisasi wajib diisi.',
        ]);

        // Verifikasi password user yang sedang login
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
            // Update status order
            $order->status = 'batal';
            $order->save();

            // Simpan record pembatalan
            PembatalanOrder::create([
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'alasan' => $request->alasan,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'no_pesanan' => $order->no_pesanan,
                'message' => "Pembatalan no WO {$order->no_pesanan} berhasil"
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