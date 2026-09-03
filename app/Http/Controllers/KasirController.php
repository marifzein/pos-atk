<?php

namespace App\Http\Controllers;

use App\Helpers\DocumentNumber;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class KasirController extends Controller
{
    /**
     * Tampilkan daftar WO berstatus 'order'
     */
    public function index(Request $request)
    {
        // $search = $request->input('search');

        // $orders = Order::with(['customer', 'operator', 'items'])
        //     ->where('status', 'order')
        //     ->when($search, function ($query, $search) {
        //         $query->where(function ($q) use ($search) {
        //             $q->where('no_pesanan', 'like', "%{$search}%")
        //               ->orWhere('customer_name_manual', 'like', "%{$search}%")
        //               ->orWhereHas('customer', function ($c) use ($search) {
        //                   $c->where('nama', 'like', "%{$search}%");
        //               });
        //         });
        //     })
        //     ->orderBy('id', 'desc')
        //     ->paginate(10);

        // return view('kasir.index', compact('orders', 'search'));

        return view('kasir.index');
    }

    /**
     * Form Transaksi / Create POS
     */   
    public function create(Request $request)
    {
        // 1. No Nota pakai Helper DocumentNumber
        $noNota = DocumentNumber::generate('transactions', 'no_nota', 'INV');

        // 2. Load Produk Aktif (is_active = 1)
        $products = Product::where('is_active', 1)
            ->get()
            ->map(function ($p) {
                return [
                    'id'             => $p->id,
                    'kode_barang'    => $p->sku ?? $p->barcode,
                    'barcode'        => $p->barcode,
                    'nama_barang'    => $p->name,
                    'purchase_price' => (float) $p->purchase_price,
                    'harga'          => (float) $p->price,
                    'stok'           => (int) $p->stock,
                    'satuan'         => $p->satuan,
                ];
            });

        // 3. Load Master Customer Aktif
        $customers = Customer::where('status', 1)->get();

        // 4. Load WO jika ada parameter order_id
        $orderData = null;
        if ($request->has('order_id')) {
            $order = Order::with(['customer', 'items.product'])->find($request->order_id);
            if ($order) {
                $orderData = [
                    'id'            => $order->id,
                    'no_pesanan'    => $order->no_pesanan,
                    'customer_id'   => $order->customer_id,
                    'customer_name' => $order->customer->nama ?? $order->customer_name_manual ?? 'Umum',
                    'items'         => $order->items->map(function ($item) {
                        return [
                            'product_id'     => $item->product_id,
                            'kode_barang'    => $item->product->sku ?? $item->product->barcode ?? 'JASA',
                            'nama_barang'    => $item->item_name ?? $item->product->name ?? 'Layanan Jasa',
                            'purchase_price' => (float) ($item->purchase_price ?? $item->product->purchase_price ?? 0),
                            'harga'          => (float) ($item->unit_price ?? 0),
                            'qty'            => (int) ($item->qty ?? 1),
                            'subtotal'       => (float) ($item->subtotal ?? 0),
                        ];
                    })->toArray()
                ];
            }
        }

        return view('kasir.create', compact('noNota', 'products', 'customers', 'orderData'));
    }

    /**
     * Endpoint Pencarian Produk/Barcode via AJAX
     */
    public function searchProduct(Request $request)
    {
        $search = $request->input('q');
        $products = Product::where('is_active', 1)
            ->where(function($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($p) {
                return [
                    'id'             => $p->id,
                    'kode_barang'    => $p->sku ?? $p->barcode,
                    'barcode'        => $p->barcode,
                    'nama_barang'    => $p->name,
                    'purchase_price' => (float) $p->purchase_price,
                    'harga'          => (float) $p->price,
                    'stok'           => (int) $p->stock,
                    'is_custom_price' => (bool) ($p->is_custom_price ?? false),
                ];
            });

        return response()->json($products);
    }

    public function storeTransaction(Request $request)
    {   
        $request->validate([
            'cart' => 'required|array|min:1',
            'grand_total' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            // 1. Generate No Nota
            $noNota = DocumentNumber::generate('transactions', 'no_nota', 'INV');

            // 2. Simpan Header Transaksi
            $transaction = Transaction::create([
                'no_nota'      => $noNota,
                'order_id'     => $request->order_id,
                'cashier_id'   => auth()->id(),
                'shift_id'    => $request->shift_id ?? 1,
                'customer_id'  => $request->pelanggan,
                'subtotal'     => $request->subtotal,
                'diskon'       => $request->diskon,
                'grand_total'  => $request->grand_total,
                'cash'         => $request->cash,
                'voucher'      => $request->voucher,
                'card'         => $request->card,
                'hutang'       => $request->hutang,
                'kembalian'    => $request->kembalian,
                
            ]);

            // 3. Simpan Detail Item & Potong Stok
            foreach ($request->cart as $item) {
                $transaction->details()->create([
                    'product_id' => $item['id'],
                    'kode_barang' => $item['kode_barang'] ,
                    'nama_barang' => $item['nama_barang'] ,
                    'qty'        => $item['qty'],
                    'harga_beli'  => $item['purchase_price'],
                    'harga'      => $item['harga'],
                    'subtotal'   => $item['qty'] * $item['harga'],
                ]);

                // Ambil data produk untuk mendapatkan stok sebelum dipotong
                $product = Product::findOrFail($item['id']);

                if (strtolower($product->type) === 'barang') {
                    $stockBefore = (int) $product->stock;
                    $qty = (int) $item['qty'];
                    $stockAfter = $stockBefore - $qty;

                    // Potong stok produk
                    $product->update(['stock' => $stockAfter]);

                    // Insert ke riwayat pergerakan stok (stock_movements)
                    DB::table('stock_movements')->insert([
                        'product_id'   => $product->id,
                        'type'         => 'PENJUALAN', // Atau 'SALE' sesuai konvensi app kamu
                        'qty'          => -$qty,       // Nilai minus menandakan barang keluar
                        'stock_before' => $stockBefore,
                        'stock_after'  => $stockAfter,
                        'reference_no' => $noNota,
                        'notes'        => 'Penjualan Kasir (Nota: ' . $noNota . ')',
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                // Potong stok produk
                // Product::where('id', $item['id'])->decrement('stock', $item['qty']);
            }

            // 4. Update status WO jika transaksi berasal dari Work Order
            if ($request->order_id) {
                Order::where('id', $request->order_id)->update(['status' => 'lunas']);
            }

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => 'Transaksi berhasil disimpan',
                'transaction_id' => $transaction->id,
                'no_nota'        => $transaction->no_nota
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    // struk nota
    public function print($id)
    {
        $transaction =
            Transaction::with('details')
            ->findOrFail($id);

        // $transaction = Transaction::with([['details', 'cashier', 'customer']])->findOrFail($id);

        $customer = null;

        if ($transaction->pelanggan) {

            $customer = Customer::where(
                // 'kode_pelanggan',
                'id',
                $transaction->pelanggan
            )->first();

        }

        // Ambil data pengaturan toko global
        $shopSetting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'nama_toko' => 'TOKO ANDA',
            'alamat' => 'Jl. Contoh No.123',
            'telepon' => '08123456789',
            'footer_nota' => 'Terima Kasih\nBarang yang sudah dibeli\ntidak dapat ditukar'
        ]);
        
        return view(
            'kasir.print',
            compact(
                'transaction',
                'customer',
                'shopSetting'
            )
        );
    }

    // list nota/transaksi
    public function show($id)
    {
        $transaction =
            // Transaction::with('details', 'user', 'pembatalan.user')
            Transaction::with(['details', 'cashier'])->findOrFail($id);

        return view(
            'kasir.show',
            compact('transaction')
        );
    }


    public function history(Request $request)
    {
        $query = Transaction::with(['cashier', 'customer']);

        // 🔒 JIKA USER LOGIN ADALAH KASIR, HANYA TAMPILKAN NOTA BUATANNYA SENDIRI
        if (auth()->user()->role === 'Kasir') {
            $query->where('cashier_id', auth()->id());
        }

        // Filter berdasarkan No. Nota
        if ($request->filled('search')) {
            $query->where('no_nota', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan Nama Pelanggan (Relasi customer)
        if ($request->filled('customer_name')) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->customer_name . '%');
            });
        }

        // Filter Rentang Tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(10)->withQueryString();

        return view('kasir.history', compact('transactions'));
    }


    // Endpoint JSON khusus Server-Side Grid.js
    public function apiOrders(Request $request)
    {
        $search = $request->input('search');
        $limit  = $request->input('limit', 10);
        $page   = $request->input('page', 1);

        $query = Order::with(['customer', 'operator', 'items', 'orderItems'])
            ->where('status', 'order')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('no_pesanan', 'like', "%{$search}%")
                        ->orWhere('customer_name_manual', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($c) use ($search) {
                            $c->where('nama', 'like', "%{$search}%");
                        });
                });
            });

        // Sorting dinamis
        if ($request->has('sort')) {
            $sortDir = $request->input('sort'); // 'asc' atau 'desc'
            $query->orderBy('id', $sortDir);
        } else {
            $query->orderBy('id', 'desc');
        }

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        // Format response sesuai JSON yang dibutuhkan Grid.js server-side
        return response()->json([
            'data' => collect($paginator->items())->map(function ($order) {
                $total = $order->orderItems?->sum('subtotal') ?? $order->items?->sum('subtotal') ?? 0;
                $pelanggan = $order->customer->nama ?? $order->customer_name_manual ?? 'Umum (Non-Member)';
                
                return [
                    $order->no_pesanan,
                    $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '-',
                    $order->operator->name ?? 'Admin',
                    $pelanggan,
                    'ORDER',
                    'Rp ' . number_format($total, 0, ',', '.'),
                    route('kasir.create', ['order_id' => $order->id])
                ];
            }),
            'total' => $paginator->total()
        ]);
    }
}