<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Product;
use App\Models\Customer;
use App\Helpers\DocumentNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderKasirController extends Controller
{
   
    public function index()
    {
        $user = Auth::user();
        // Strict branch check: Tolak user gelap/tanpa cabang
        abort_if(!$user->branch_id || !$user->branch, 403, 'Akses ditolak: Akun tidak terikat dengan cabang resmi.');

        $branchId = $user->branch_id ;
        $branchName = $user->branch->nama_cabang ?? $user->branch->name ;


        // Nomor SP baru (Preview di view)
        $previewSp = DocumentNumber::generate_custom('orders', 'no_pesanan', 'SP', $branchId);

        // Ambil data untuk Alpine autocomplete (sama persis seperti KasirController)
        $products = Product::where('is_active', 1)->get(['id', 'barcode', 'sku', 'name', 'price', 'purchase_price', 'is_custom_price']);
        $customers = Customer::get(['id', 'kode_pelanggan', 'nama', 'telepon', 'alamat']);

        return view('order-kasir.index', compact('previewSp', 'products', 'customers', 'branchName'));
    }

    public function searchProducts(Request $request)
    {
        $query = $request->get('q');
        $products = Product::where('status', 'aktif')
            ->where(function ($w) use ($query) {
                $w->where('barcode', 'like', "%{$query}%")
                  ->orWhere('kode_barang', 'like', "%{$query}%")
                  ->orWhere('nama_barang', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['id', 'barcode', 'sku', 'name', 'price', 'purchase_price']);

        return response()->json($products);
    }

    public function searchCustomers(Request $request)
    {
        $query = $request->get('q');
        $customers = Customer::where('nama', 'like', "%{$query}%")
            ->orWhere('no_telp', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'kode_pelanggan', 'nama', 'telepon', 'alamat']);

        return response()->json($customers);
    }

    

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'           => 'required|exists:customers,id',
            'tgl_estimasi_selesai'  => 'required|date',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.qty'           => 'required|numeric|min:1',
            'items.*.price'         => 'required|numeric|min:0',
            'nominal_bayar'         => 'required|numeric|min:0',
            'metode_pembayaran'     => 'required|in:cash,card,qris,transfer',
        ], [
            'customer_id.required'          => 'Pelanggan wajib dipilih!',
            'tgl_estimasi_selesai.required' => 'Tanggal estimasi selesai wajib diisi!',
            'items.required'                => 'Keranjang pesanan masih kosong!',
        ]);

        $user = Auth::user();
        abort_if(!$user->branch_id, 403, 'Akses ditolak: Akun tidak terikat dengan cabang.');
        $branchId = $user->branch_id;

        // 1. Strict Check: Cari shift aktif kasir di tabel shifts (status = 'open')
        $activeShift = DB::table('shifts')
            ->where('user_id', $user->id)
            ->where('branch_id', $branchId)
            ->where('status', 'open')
            ->first();

        // 2. Wajib buka shift sebelum transaksi
        if (!$activeShift) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Akses ditolak! Anda belum membuka shift kasir atau shift Anda sudah ditutup.'
            ], 403);
        }

        $activeShiftId = $activeShift->id;

        try {
            return DB::transaction(function () use ($request, $user, $branchId, $activeShiftId) {
                // 1. Hitung total transaksi dari array item
                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $totalAmount += ($item['qty'] * $item['price']);
                }

                $nominalBayar = (float) $request->nominal_bayar;

                // 2. Tentukan status pembayaran
                if ($nominalBayar <= 0) {
                    $paymentStatus = 'unpaid';
                } elseif ($nominalBayar < $totalAmount) {
                    $paymentStatus = 'dp';
                } else {
                    $paymentStatus = 'lunas';
                }

                // 3. Generate Nomor Surat Pesanan (SP)
                $noSp = DocumentNumber::generate_custom('orders', 'no_pesanan', 'SP', $branchId);

                // 4. Insert ke tabel orders
                $order = Order::create([
                    'branch_id'             => $branchId,
                    'order_source'          => 'kasir',
                    'no_pesanan'            => $noSp,
                    'operator_id'           => $user->id,
                    'customer_id'           => $request->customer_id,
                    'status'                => ($paymentStatus === 'lunas') ? 'lunas' : 'order',
                    'payment_status'        => $paymentStatus,
                    'catatan'               => $request->catatan,
                    'tgl_estimasi_selesai'  => $request->tgl_estimasi_selesai,
                    'total_amount'          => $totalAmount,
                ]);

                // 5. Insert rincian ke order_items
                foreach ($request->items as $item) {
                    OrderItem::create([
                        'order_id'       => $order->id,
                        'product_id'     => $item['product_id'],
                        'item_name'      => $item['name'],
                        'qty'            => $item['qty'],
                        'purchase_price' => $item['purchase_price'] ?? 0,
                        'unit_price'     => $item['price'],
                        'subtotal'       => $item['qty'] * $item['price'],
                        'notes'          => $item['notes'] ?? null,
                        'operator_id'    => $user->id,
                    ]);
                }

                // 6. Jika ada pembayaran (DP atau Langsung Lunas), catat ke order_payments
                $payment = null;
                if ($nominalBayar > 0) {
                    $payment = OrderPayment::create([
                        'branch_id'         => $branchId,
                        'order_id'          => $order->id,
                        'cashier_id'        => $user->id,
                        'shift_id'          => $activeShiftId,
                        'no_bukti_bayar'    => $noSp,
                        'payment_type'      => ($paymentStatus === 'lunas') ? 'PELUNASAN' : 'DP',
                        'metode_pembayaran' => $request->metode_pembayaran,
                        'nominal'           => min($nominalBayar, $totalAmount),
                        'bank_name'         => $request->bank_name,
                        'ref_no'            => $request->ref_no,
                        'catatan'           => $request->payment_notes ?? 'Penerimaan DP/Kasir Awal',
                    ]);
                }

                return response()->json([
                    'status'         => 'success',
                    'message'        => 'Order Kasir berhasil disimpan.',
                    'order_id'       => $order->id,
                    'no_pesanan'     => $order->no_pesanan,
                    'no_sp'          => $payment ? $payment->no_bukti_bayar : null,
                    'payment_status' => $paymentStatus,
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan pesanan: ' . $e->getMessage()
            ], 500);
        }
    }


    public function print($id)
    {
        $order = Order::with(['customer', 'operator', 'items', 'payments', 'branch'])->findOrFail($id);

        $totalDp = (float) $order->payments()->sum('nominal');
        $sisaTagihan = max(0, (float) $order->total_amount - $totalDp);

        // Ambil pengaturan toko langsung dari tabel settings
        $shopSetting = \App\Models\Setting::first();

        return view('order-kasir.print', compact('order', 'totalDp', 'sisaTagihan', 'shopSetting'));
    }
}