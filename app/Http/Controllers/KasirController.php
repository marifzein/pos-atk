<?php

namespace App\Http\Controllers;

use App\Helpers\DocumentNumber;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\Customer;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
        // 1. No Nota pakai Helper DocumentNumber NP Nota Penjualan
        $noNota = DocumentNumber::generate('transactions', 'no_nota', 'NP');

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
            $order = Order::with(['customer', 'items.product', 'operator'])->find($request->order_id);
            if ($order) {
                $orderData = [
                    'id'            => $order->id,
                    'no_pesanan'    => $order->no_pesanan,
                    'customer_id'   => $order->customer_id,
                    'customer_name' => $order->customer->nama ?? $order->customer_name_manual ?? 'Umum',
                    'operator_name' => $order->operator->name ,
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

         $user = Auth::user();

        // STRICT CHECK: Tolak simpan jika tidak ada branch_id
        if (!$user || !$user->branch_id) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, cabang tidak terdeteksi. Silahkan login ulang.'
            ], 422);
        }

        
        $branchId = $user->branch_id;

        DB::beginTransaction();
        try {

            // 🔒 DEEP SECURITY: Pengecekan Cabang Sebelum Simpan Transaksi
            if ($request->order_id) {
                $checkOrder = Order::find($request->order_id);
                if ($checkOrder && $checkOrder->branch_id != $user->branch_id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akses ditolak! Pembayaran WO hanya bisa dilakukan oleh user dari cabang yang sama.'
                    ], 403);
                }
            }

            // 1. Generate No Nota
            $noNota = DocumentNumber::generate('transactions', 'no_nota', 'NP');

            // 2. Simpan Header Transaksi
            $transaction = Transaction::create([
                'no_nota'      => $noNota,
                'branch_id'    => $branchId,
                'order_id'     => $request->order_id,
                'cashier_id'   => $user->id,    
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
                    
                    $qty = (int) $item['qty'];

                    // A. Ambil atau inisialisasi record produk di product_stocks sesuai cabang
                    $productStock = DB::table('product_stocks')
                        ->where('product_id', $product->id)
                        ->where('branch_id', $branchId)
                        ->first();

                    $stockBefore = $productStock ? (int) $productStock->stock : 0;
                    $stockAfter = $stockBefore - $qty;

                    








                    if ($productStock) {
                    // Update stok cabang
                    DB::table('product_stocks')
                        ->where('id', $productStock->id)
                        ->update([
                            'stock'      => $stockAfter,
                            'updated_at' => now(),
                        ]);
                    } else {
                        // Jika belum ada row stok cabang, buat baru
                        DB::table('product_stocks')->insert([
                            'product_id' => $product->id,
                            'branch_id'  => $branchId,
                            'stock'      => $stockAfter,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }





                    // Insert ke riwayat pergerakan stok (stock_movements)
                    DB::table('stock_movements')->insert([
                        'branch_id'    => $branchId,
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
    public function print_lama($id)
    {
        $transaction =
            Transaction::with('details', 'cashier')
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
            'telepon' => '-------',
            'footer_nota' => 'Terima Kasih Barang yang sudah dibeli tidak dapat ditukar'
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
        $transaction = Transaction::with([
            'details', 
            'cashier', 
            'branch', 
            'customer', 
            'order.payments.cashier'
        ])->findOrFail($id);

        return view('kasir.show', compact('transaction'));
    }


    public function history(Request $request)
    {
        $query = Transaction::with(['details', 'cashier', 'customer', 'branch']);

        // 🔒 JIKA USER LOGIN ADALAH KASIR, HANYA TAMPILKAN NOTA BUATANNYA SENDIRI
        if (auth()->user()->role === 'Kasir') {
            $query->where('cashier_id', auth()->id());
        }

        // 🏪 FILTER CABANG & HAK AKSES
        $branches = Branch::where('is_active', 1)->get();
        $selectedBranchId = $request->get('branch_id');

         // Filter berdasarkan branch
        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
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

        return view('kasir.history', compact('transactions','branches','selectedBranchId'));
    }

    

    
    

    // Endpoint JSON khusus Server-Side Grid.js
    public function apiOrders(Request $request)
    {
        $search = $request->input('search');
        $limit  = $request->input('limit', 10);
        $page   = $request->input('page', 1);

        // Cari order yang belum pernah diterbitkan Nota Penjualan (NP) di tabel transactions
        $query = Order::with(['branch', 'customer', 'operator', 'items', 'orderItems', 'payments'])
            ->where('status', '!=', 'batal')
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('transactions')
                    ->whereRaw('transactions.order_id = orders.id');
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('no_pesanan', 'like', "%{$search}%")
                        ->orWhere('customer_name_manual', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($c) use ($search) {
                            $c->where('nama', 'like', "%{$search}%");
                        });
                });
            });

        // JIKA USER ADALAH KASIR, HANYA TAMPILKAN WO DI CABANG SI KASIR
        $user = auth()->user();
        if ($user && in_array(strtolower($user->role), ['kasir'])) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->has('sort')) {
            $sortDir = $request->input('sort');
            $query->orderBy('id', $sortDir);
        } else {
            $query->orderBy('id', 'desc');
        }

        $paginator = $query->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'data' => collect($paginator->items())->map(function ($order) use ($user) {
                $total = $order->total_amount > 0 
                    ? (float) $order->total_amount 
                    : (float) ($order->orderItems?->sum('subtotal') ?? $order->items?->sum('subtotal') ?? 0);

                $totalDp = (float) $order->payments?->sum('nominal');
                $sisaTagihan = max(0, $total - $totalDp);

                $pelanggan = $order->customer->nama ?? $order->customer_name_manual ?? 'Umum (Non-Member)';
                
                $firstItemObj = $order->orderItems?->first() ?? $order->items?->first();
                $firstItemName = $firstItemObj?->item_name ?? $firstItemObj?->product?->name ?? '-';

                $isSameBranch = ($user && $user->branch_id == $order->branch_id);
                $isKasirOrder = ($order->order_source === 'kasir');

                // Tentukan URL tujuan:
                // Jika pesanan sudah lunas di awal (sisaTagihan <= 0), arahkan langsung ke pembuatan NP (kasir.create)
                // Jika masih ada sisa tagihan pada SP kasir, arahkan ke pelunasan
                // if ($sisaTagihan <= 0) {
                //     $targetUrl = route('kasir.create', ['order_id' => $order->id]);
                // } else {
                //     $targetUrl = $isKasirOrder 
                //         ? route('kasir.pelunasan', $order->id) 
                //         : route('kasir.create', ['order_id' => $order->id]);
                // }
                // Arahkan SP kasir ke halaman pelunasan/penyerahan, WO staf ke kasir.create
                $targetUrl = $isKasirOrder 
                    ? route('kasir.pelunasan', $order->id) 
                    : route('kasir.create', ['order_id' => $order->id]);

                return [
                    [
                        'no_pesanan'   => $order->no_pesanan,
                        'branch_name'  => $order->branch->nama_cabang ?? $order->branch->name ?? '-',
                        'order_source' => $order->order_source
                    ],
                    [
                        'tanggal' => $order->created_at ? $order->created_at->format('Y-m-d') : '-',
                        'jam'     => $order->created_at ? $order->created_at->format('H:i:s') : '-'
                    ],
                    $firstItemName,
                    $order->operator->name ?? 'Admin',
                    $pelanggan,
                    [
                        'status'         => strtoupper($order->status),
                        'payment_status' => $order->payment_status,
                        'total_dp'       => $totalDp
                    ],
                    [
                        'total'  => 'Rp ' . number_format($total, 0, ',', '.'),
                        'sisa'   => 'Rp ' . number_format($sisaTagihan, 0, ',', '.'),
                        'has_dp' => $totalDp > 0
                    ],
                    [
                        'url'            => $targetUrl,
                        'is_same_branch' => $isSameBranch,
                        'is_kasir_order' => $isKasirOrder,
                        'is_lunas'       => ($sisaTagihan <= 0)
                    ]
                ];
            }),
            'total' => $paginator->total()
        ]);
    }

    /**
     * Halaman View Pelunasan Khusus Surat Pesanan (SP) Inden
     */
    public function pelunasan($id)
    {
        $user = Auth::user();

        // 1. Strict Security Check: Wajib punya branch_id
        if (!$user || !$user->branch_id) {
            abort(403, 'Akses ditolak: Cabang tidak terdeteksi.');
        }

        // 2. Load order beserta relasinya
        $order = Order::with(['items.product', 'orderItems.product', 'customer', 'branch', 'payments'])
            ->where('branch_id', $user->branch_id)
            ->findOrFail($id);

        // 3. Validasi status
        // if ($order->status === 'lunas' || $order->payment_status === 'lunas') {
        //     return redirect()->route('kasir.index')->with('info', 'Pesanan ini sudah lunas.');
        // }
        // UBAH VALIDASI INI: Hanya tolak jika NOTA PENJUALAN (NP) SUDAH TERBIT di tabel transactions
        $sudahAdaNota = Transaction::where('order_id', $order->id)->exists();
        if ($sudahAdaNota) {
            return redirect()->route('kasir.index')->with('info', 'Nota Penjualan untuk pesanan ini sudah pernah diterbitkan.');
        }

        // 4. Hitung Total DP yang pernah masuk & sisa tagihan
        $totalDp = (float) $order->payments()->sum('nominal');
        $totalKontrak = (float) $order->total_amount;

        // Fallback jika total_amount di header masih 0
        if ($totalKontrak <= 0) {
            $totalKontrak = (float) ($order->orderItems?->sum('subtotal') ?? $order->items?->sum('subtotal') ?? 0);
        }

        $sisaTagihan = max(0, $totalKontrak - $totalDp);
        $branchName = $order->branch->nama_cabang ?? $order->branch->name ?? 'Cabang';

        return view('kasir.pelunasan', compact('order', 'totalDp', 'totalKontrak', 'sisaTagihan', 'branchName'));
    }

    /**
     * Eksekusi Simpan Pelunasan SP Inden
     */
    public function storePelunasan_lawas(Request $request, $id)
    {
        $request->validate([
            'nominal_bayar'     => 'required|numeric|min:0',
            'metode_pembayaran' => 'required|in:cash,card,qris,transfer',
        ]);

        $user = Auth::user();
        if (!$user || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Cabang tidak terdeteksi.'], 403);
        }

        $branchId = $user->branch_id;
        $activeShiftId = session('active_shift_id', 1);

        DB::beginTransaction();
        try {
            $order = Order::with(['items', 'orderItems', 'payments'])
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->findOrFail($id);

            // Hitung total kontrak & DP lama
            $totalAmount = (float) $order->total_amount;
            if ($totalAmount <= 0) {
                $totalAmount = (float) ($order->orderItems?->sum('subtotal') ?? $order->items?->sum('subtotal') ?? 0);
            }

            $totalDp = (float) $order->payments()->sum('nominal');
            $sisaWajib = max(0, $totalAmount - $totalDp);
            $nominalBayar = (float) $request->nominal_bayar;

            if ($nominalBayar < $sisaWajib) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal bayar kurang dari sisa tagihan (Rp ' . number_format($sisaWajib, 0, ',', '.') . ')'
                ], 422);
            }

            $kembalian = max(0, $nominalBayar - $sisaWajib);

            // 2. Generate No Nota Penjualan (NP)
            $noNota = DocumentNumber::generate('transactions', 'no_nota', 'NP');

            // 1. Catat kas masuk pelunasan ke order_payments jika sisaWajib > 0
            if ($sisaWajib > 0) {
                \App\Models\OrderPayment::create([
                    'branch_id'         => $branchId,
                    'order_id'          => $order->id,
                    'cashier_id'        => $user->id,
                    'shift_id'          => $activeShiftId,
                    'no_bukti_bayar'    => $noNota,
                    'payment_type'      => 'PELUNASAN',
                    'metode_pembayaran' => $request->metode_pembayaran,
                    'nominal'           => $sisaWajib,
                    'bank_name'         => $request->bank_name,
                    'ref_no'            => $request->ref_no,
                    'catatan'           => 'Pelunasan SP: ' . $order->no_pesanan,
                ]);
            }

            

            // 3. Simpan Transaksi Penjualan Utama (sesuai kolom tabel transactions POS Anda)
            $transaction = Transaction::create([
                'no_nota'      => $noNota,
                'branch_id'    => $branchId,
                'order_id'     => $order->id,
                'cashier_id'   => $user->id,
                'shift_id'     => $activeShiftId,
                'customer_id'  => $order->customer_id,
                'subtotal'     => $totalAmount,
                'diskon'       => 0,
                'grand_total'  => $totalAmount,
                'cash'         => ($request->metode_pembayaran === 'cash') ? $nominalBayar : 0,
                'voucher'      => 0,
                'card'         => in_array($request->metode_pembayaran, ['card', 'qris', 'transfer']) ? $sisaWajib : 0,
                'hutang'       => 0,
                'kembalian'    => $kembalian,
            ]);

            // 4. Salin Item ke details transaksi (mengakomodasi orderItems atau items)
            $rawItems = $order->orderItems->isNotEmpty() ? $order->orderItems : $order->items;
            foreach ($rawItems as $item) {
                $transaction->details()->create([
                    'product_id'   => $item->product_id,
                    'kode_barang'  => $item->product->sku ?? $item->product->barcode ?? 'JASA',
                    'nama_barang'  => $item->item_name ?? $item->product->name ?? 'Layanan Jasa',
                    'qty'          => $item->qty,
                    'harga_beli'   => $item->purchase_price ?? 0,
                    'harga'        => $item->unit_price ?? $item->price ?? 0,
                    'subtotal'     => $item->subtotal,
                ]);
            }

            // 5. Update Status Order menjadi Selesai & Lunas
            $order->update([
                'status'         => 'lunas',
                'payment_status' => 'lunas'
            ]);

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => 'Pelunasan berhasil diproses.',
                'transaction_id' => $transaction->id,
                'no_nota'        => $noNota
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pelunasan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storePelunasan(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$user->branch_id) {
            return response()->json(['success' => false, 'message' => 'Cabang tidak terdeteksi.'], 403);
        }

        $branchId = $user->branch_id;
        $activeShiftId = session('active_shift_id', 1);

        DB::beginTransaction();
        try {
            $order = Order::with(['items', 'orderItems', 'payments'])
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->findOrFail($id);

            // Cek apakah sudah pernah terbit NP
            if (Transaction::where('order_id', $order->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Nota Penjualan untuk pesanan ini sudah pernah diproses.'], 422);
            }

            $totalAmount = (float) $order->total_amount;
            if ($totalAmount <= 0) {
                $totalAmount = (float) ($order->orderItems?->sum('subtotal') ?? $order->items?->sum('subtotal') ?? 0);
            }

            $totalDp = (float) $order->payments()->sum('nominal');
            $sisaWajib = max(0, $totalAmount - $totalDp);
            $nominalBayar = (float) ($request->nominal_bayar ?? 0);

            // Validasi jika memang masih ada sisa tagihan
            if ($sisaWajib > 0 && $nominalBayar < $sisaWajib) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal bayar kurang dari sisa tagihan (Rp ' . number_format($sisaWajib, 0, ',', '.') . ')'
                ], 422);
            }

            $kembalian = max(0, $nominalBayar - $sisaWajib);
            $noNota = DocumentNumber::generate('transactions', 'no_nota', 'NP');

            // HANYA simpan ke order_payments jika ada penerimaan uang baru hari ini
            if ($sisaWajib > 0) {
                \App\Models\OrderPayment::create([
                    'branch_id'         => $branchId,
                    'order_id'          => $order->id,
                    'cashier_id'        => $user->id,
                    'shift_id'          => $activeShiftId,
                    'no_bukti_bayar'    => $noNota,
                    'payment_type'      => 'PELUNASAN',
                    'metode_pembayaran' => $request->metode_pembayaran ?? 'cash',
                    'nominal'           => $sisaWajib,
                    'bank_name'         => $request->bank_name,
                    'ref_no'            => $request->ref_no,
                    'catatan'           => 'Pelunasan SP: ' . $order->no_pesanan,
                ]);
            }

            // Simpan Transaksi Penjualan Utama (Faktur NP)
            $transaction = Transaction::create([
                'no_nota'      => $noNota,
                'branch_id'    => $branchId,
                'order_id'     => $order->id,
                'cashier_id'   => $user->id,
                'shift_id'     => $activeShiftId,
                'customer_id'  => $order->customer_id,
                'subtotal'     => $totalAmount,
                'diskon'       => 0,
                'grand_total'  => $totalAmount,
                // Jika sudah lunas saat SP, cash/card penerimaan hari ini diisi 0 (karena uang masuknya sudah diakui tanggal pesan)
                'cash'         => ($sisaWajib > 0 && $request->metode_pembayaran === 'cash') ? $nominalBayar : 0,
                'voucher'      => 0,
                'card'         => ($sisaWajib > 0 && in_array($request->metode_pembayaran, ['card', 'qris', 'transfer'])) ? $sisaWajib : 0,
                'hutang'       => 0,
                'kembalian'    => $kembalian,
                'status'       => 'LUNAS'
            ]);

            // Salin item pesanan ke details nota
            $rawItems = $order->orderItems->isNotEmpty() ? $order->orderItems : $order->items;
            foreach ($rawItems as $item) {
                $transaction->details()->create([
                    'product_id'   => $item->product_id,
                    'kode_barang'  => $item->product->sku ?? $item->product->barcode ?? 'JASA',
                    'nama_barang'  => $item->item_name ?? $item->product->name ?? 'Layanan Jasa',
                    'qty'          => $item->qty,
                    'harga_beli'   => $item->purchase_price ?? 0,
                    'harga'        => $item->unit_price ?? $item->price ?? 0,
                    'subtotal'     => $item->subtotal,
                ]);
            }

            // Finalisasi status order
            $order->update([
                'status'         => 'lunas',
                'payment_status' => 'lunas'
            ]);

            DB::commit();

            return response()->json([
                'success'        => true,
                'message'        => 'Nota Penjualan berhasil diterbitkan.',
                'transaction_id' => $transaction->id,
                'no_nota'        => $noNota
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses nota: ' . $e->getMessage()
            ], 500);
        }
    }

    // struk nota
    public function print($id)
    {
        $transaction = Transaction::with(['details', 'cashier', 'branch', 'order.branch'])
            ->findOrFail($id);

        $customer = null;
        if ($transaction->customer_id ?? $transaction->pelanggan) {
            $customerId = $transaction->customer_id ?? $transaction->pelanggan;
            $customer = Customer::find($customerId);
        }

        // Ambil data pengaturan toko global
        $shopSetting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'nama_toko'   => 'CAHAYA BUSUR GROUP',
            'alamat'      => 'Jl. Contoh No.123',
            'telepon'     => '-------',
            'footer_nota' => 'Terima Kasih, barang yang sudah dibeli tidak dapat ditukar kembali'
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
    
    /**
     * Struk Cetak Nota Penjualan (NP) Hasil Pelunasan SP
     */
    public function printPelunasan($transactionId)
    {
        $transaction = Transaction::with(['details', 'cashier', 'order.payments'])->findOrFail($transactionId);
        $order = $transaction->order;

        $customer = null;
        if ($transaction->customer_id) {
            $customer = Customer::find($transaction->customer_id);
        }

        // Hitung total DP dari order
        $totalDp = 0;
        if ($order) {
            $totalDp = (float) $order->payments->where('payment_type', 'DP')->sum('nominal');
        }

        $sisaDilunasi = max(0, (float) $transaction->grand_total - $totalDp);
        $nominalBayarHariIni = ((float) $transaction->cash > 0) ? (float) $transaction->cash : ((float) $transaction->card > 0 ? (float) $transaction->card : $sisaDilunasi);

        $shopSetting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'nama_toko'   => 'TOKO ANDA',
            'alamat'      => 'Jl. Contoh No.123',
            'telepon'     => '-------',
            'footer_nota' => 'Terima Kasih Atas Kepercayaan Anda'
        ]);

        return view('kasir.print_pelunasan', compact(
            'transaction',
            'order',
            'customer',
            'totalDp',
            'sisaDilunasi',
            'nominalBayarHariIni',
            'shopSetting'
        ));
    }

   
}