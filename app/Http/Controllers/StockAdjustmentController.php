<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\StockAdjustmentDetail;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DocumentNumber;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = StockAdjustment::with('user')->latest()->paginate(10);
        $branches = DB::table('branches')->where('is_active', 1)->get(); // Ambil data cabang aktif

        return view('stock-adjustments.index', compact('adjustments', 'branches'));
    }

    public function create(Request $request)
    {
        $branchId = (int) $request->get('branch_id', 1);
        $branch = DB::table('branches')->find($branchId);

        // Generate Nomor SA otomatis (Format: SA-YYYYMMDD-0001)
        $nomor_sa = DocumentNumber::generate('stock_adjustments', 'nomor_sa', 'SA',$branchId);    
        $products = Product::where('is_active', 1)->orderBy('name')->get();

        return view('stock-adjustments.create', compact('nomor_sa', 'products','branch'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input (Sesuai dengan name attribute HTML input array)
        $request->validate([
            // 'nomor_sa'     => 'required|unique:stock_adjustments,nomor_sa',
            'branch_id'    => 'required',
            'tgl_sa'       => 'required|date',
            'product_id'   => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'qty'          => 'required|array|min:1',
            'qty.*'        => 'required|integer|min:1',
            'notes'        => 'nullable|array',
        ], [
            'product_id.required' => 'Minimal harus ada 1 barang yang disesuaikan!',
            'qty.*.min'           => 'Qty minimal 1!'
        ]);

        // Menentukan status berdasarkan value tombol submit yang diklik
        $status = $request->input('action') === 'closed' ? 'closed' : 'draft';
        $branchId = (int) $request->branch_id;
        try {
            $fixNomorSa = null;    
            DB::transaction(function () use ($request, $status, $branchId) {

                // generate_custom

                // 🔥 LANGKAH KRUSIAL: Generate nomor SA yang sesungguhnya langsung dari Helper!
                // Karena berada di dalam DB::transaction, nomor ini dijamin aman dan anti-double.
                // $fixNomorSa = \App\Helpers\DocumentNumber::generate('stock_adjustments', 'nomor_sa', 'SA');
                
                $fixNomorSa = DocumentNumber::generate_custom(
                'stock_adjustments', 
                'nomor_sa',     
                'SA',                
                $branchId
            );
                


                // 2. Simpan Master Dokumen (Status tersimpan sesuai klik: draft / closed)
                $sa = StockAdjustment::create([
                    'branch_id'       => $branchId,
                    'nomor_sa'        => $fixNomorSa,
                    'tgl_sa'          => $request->tgl_sa,
                    'user_id'         => Auth::id(),
                    'status'          => $status,
                    'catatan'         => $request->catatan,
                    'tgl_jam_selesai' => $status === 'closed' ? now() : null,
                ]);

                // 3. Loop Item Detail
                foreach ($request->product_id as $index => $productId) {
                    $product   = Product::findOrFail($productId);
                    $qtyAdjust = (int) $request->qty[$index];
                    $itemNotes = $request->notes[$index] ?? null;

                    // $stockBefore = $product->stok;
                    // $stockAfter  = $stockBefore - $qtyAdjust;
                    // Ambil stok dari product_stocks cabang
                    $pStock = DB::table('product_stocks')
                        ->where('branch_id', $branchId)
                        ->where('product_id', $productId)
                        ->first();

                    $stockBefore = $pStock ? (int)$pStock->stock : 0;
                    $stockAfter  = $stockBefore - $qtyAdjust;

                    // FIX: Menggunakan $sa->id (properti), BUKAN $sa->id() (method)
                    StockAdjustmentDetail::create([
                        'stock_adjustment_id' => $sa->id, 
                        'product_id'          => $productId,
                        'stock_system'        => $stockBefore,
                        'qty'                 => $qtyAdjust,
                        'notes'               => $itemNotes,
                    ]);

                    // Jika tombolnya "Posting & Kunci Stok", jalankan potong stok & mutasi barang
                    if ($status === 'closed') {
                        if ($pStock) {
                            DB::table('product_stocks')
                                ->where('id', $pStock->id)
                                ->update([
                                    'stock'      => $stockAfter,
                                    'updated_at' => now(),
                                ]);
                        } else {
                            DB::table('product_stocks')->insert([
                                'branch_id'  => $branchId,
                                'product_id' => $productId,
                                'stock'      => $stockAfter,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        DB::table('stock_movements')->insert([
                            'branch_id'    => $branchId,
                            'product_id'   => $product->id,
                            'type'         => 'Stock Adjustment',
                            'qty'          => -$qtyAdjust,
                            'stock_before' => $stockBefore,
                            'stock_after'  => $stockAfter,
                            'reference_no' => $sa->nomor_sa,
                            'notes'        => $itemNotes ?? 'Adjustment (' . $sa->nomor_sa . ')',
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }
                }
            });

            $message = $status === 'closed' 
                    ? 'Stock Adjustment berhasil diposting!' 
                    : 'Draft Stock Adjustment berhasil disimpan ke database.';
            
            // --- KUNCI SINKRONISASI UX DI SINI ---
            // Jika request dikirim via Fetch/AJAX (F10), kembalikan JSON respon agar terbaca oleh Javascript SweetAlert2
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }
            
            // Jika submit form biasa (F7 / Draft), redirect seperti biasa
            return redirect()->route('stock-adjustments.index')->with('success', $message);
        
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
        
    }

    public function post(StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status === 'closed') {
            return back()->with('error', 'Dokumen ini sudah diposting sebelumnya.');
        }

        DB::transaction(function () use ($stockAdjustment) {
            // Muat ulang item detail
            $details = $stockAdjustment->details()->with('product')->get();

            foreach ($details as $detail) {
                $product = $detail->product;
                
                $stockBefore = $product->stok;
                $stockAfter = $stockBefore - $detail->qty; // Selalu Mengurangi Stok sesuai kesepakatan bisnis

                // A. Potong Stok Utama di Tabel Products
                $product->update([
                    'stok' => $stockAfter
                ]);

                // B. Suntik Riwayat Perubahan ke Tabel stock_movements milik kamu
                DB::table('stock_movements')->insert([
                    'product_id' => $product->id,
                    'type' => 'Stock Adjustment',
                    'qty' => -$detail->qty, // disimpan minus karena mengurangi stok
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_no' => $stockAdjustment->nomor_sa,
                    'notes' => $detail->notes ?? 'Stock adjustment dari dokumen ' . $stockAdjustment->nomor_sa,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // C. Kunci Dokumen & Update Status Jadi Closed
            $stockAdjustment->update([
                'status' => 'closed',
                'tgl_jam_selesai' => now()
            ]);
        });

        return redirect()->route('stock-adjustments.index')->with('success', 'Dokumen SA berhasil diposting! Stok produk telah disesuaikan.');
    }
    // edit
    public function edit(StockAdjustment $stockAdjustment)
    {
        // Cegah edit jika status dokumen sudah closed / dikunci
        // if ($stockAdjustment->status === 'closed') {
        //     return redirect()->route('stock-adjustments.index')
        //         ->with('error', 'Dokumen yang sudah diposting tidak dapat diubah kembali.');
        // }

        // Muat data detail produk yang terikat dengan adjustment ini
        // $details = $stockAdjustment->details()->with('product')->get();
        $stockAdjustment->load(['branch', 'details.product']);

        // Transformasi ke format JSON/Array agar bisa dibaca oleh JavaScript cart di Blade
        $cartData = $stockAdjustment->details->map(function ($detail) {
            $product = $detail->product;
            return [
                'id'    => $detail->product_id,
                'name'  => $product?->name ?? $product?->nama_barang ?? 'Produk ID #' . $detail->product_id,
                'code'  => $product?->sku ?? $product?->barcode ?? $product?->kode_barang ?? '-',
                'qty'   => (int) $detail->qty,
                'notes' => $detail->notes ?? ''
            ];
        });

        return view('stock-adjustments.edit', compact('stockAdjustment', 'cartData'));
    }

    // update
    public function update2(Request $request, StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status === 'closed') {
            return redirect()->route('stock-adjustments.index')
                ->with('error', 'Dokumen sudah <diclosing></diclosing>.');
        }

        // 1. Validasi Input
        $request->validate([
            'tgl_sa'       => 'required|date',
            'catatan'      => 'nullable|string',
            'product_id'   => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'qty'          => 'required|array|min:1',
            'qty.*'        => 'required|integer|min:1',
            'notes'        => 'required|array|min:1',
            'notes.*'      => 'required|string',
        ], [
            'product_id.required' => 'Wajib memilih minimal 1 produk!',
            'qty.*.min' => 'Kuantitas tidak boleh kurang dari 1!',
            'notes.*.required' => 'Alasan wajib diisi!'
        ]);

        $status = $request->input('action') === 'closed' ? 'closed' : 'draft';
        $branchId = (int) $stockAdjustment->branch_id;

        try {
            DB::transaction(function () use ($request, $stockAdjustment, $status) {
                // 2. Update Master Dokumen
                $stockAdjustment->update([
                    'tgl_sa'          => $request->tgl_sa,
                    'status'          => $status,
                    'catatan'         => $request->catatan,
                    'tgl_jam_selesai' => $status === 'closed' ? now() : null,
                ]);

                // 3. Hapus detail lama
                $stockAdjustment->details()->delete();

                // 4. Insert Detail Baru & Eksekusi Potong Stok jika status berubah jadi Closed
                foreach ($request->product_id as $index => $productId) {
                    // $product   = Product::findOrFail($productId);
                    $qtyAdjust = (int) $request->qty[$index];
                    $itemNotes = $request->notes[$index] ?? null;

                    // Ambil stok komputer dari tabel product_stocks sesuai cabang dokumen
                    $pStock = DB::table('product_stocks')
                        ->where('branch_id', $branchId  )
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();

                    // Pastikan default 0 jika belum ada catatan stok, jangan biarkan NULL
                    $stockBefore = $pStock ? (int) $pStock->stock : 0;
                    $stockAfter  = $stockBefore - $qtyAdjust;

                    StockAdjustmentDetail::create([
                        'stock_adjustment_id' => $stockAdjustment->id,
                        'product_id'          => $productId,
                        'stock_system'        => $stockBefore,
                        'qty'                 => $qtyAdjust,
                        'notes'               => $itemNotes,
                    ]);

                    if ($status === 'closed') {
                        $product->update(['stok' => $stockAfter]);

                        DB::table('stock_movements')->insert([
                            'product_id'   => $product->id,
                            'type'         => 'Stock Adjustment',
                            'qty'          => -$qtyAdjust,
                            'stock_before' => $stockBefore,
                            'stock_after'  => $stockAfter,
                            'reference_no' => $stockAdjustment->nomor_sa,
                            'notes'        => $itemNotes ?? 'Adjustment barang rusak/expired (' . $stockAdjustment->nomor_sa . ')',
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }
                }
            });

            $message = $status === 'closed' ? 'Stock Adjustment berhasil diposting!' : 'Perubahan Draft berhasil disimpan.';

            // Response JSON untuk request AJAX (F10)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return redirect()->route('stock-adjustments.index')->with('success', $message);

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    // update
    public function update(Request $request, StockAdjustment $stockAdjustment)
    {
        if ($stockAdjustment->status === 'closed') {
            return redirect()->route('stock-adjustments.index')
                ->with('error', 'Dokumen sudah dikunci.');
        }

        // 1. Validasi Input
        $request->validate([
            'tgl_sa'       => 'required|date',
            'catatan'      => 'nullable|string',
            'product_id'   => 'required|array|min:1',
            'product_id.*' => 'required|exists:products,id',
            'qty'          => 'required|array|min:1',
            'qty.*'        => 'required|integer|min:1',
            'notes'        => 'required|array|min:1',
            'notes.*'      => 'required|string',
        ], [
            'product_id.required' => 'Wajib memilih minimal 1 produk!',
            'qty.*.min'           => 'Kuantitas tidak boleh kurang dari 1!',
            'notes.*.required'    => 'Alasan wajib diisi!'
        ]);

        $status   = $request->input('action') === 'closed' ? 'closed' : 'draft';
        $branchId = (int) $stockAdjustment->branch_id;

        try {
            DB::transaction(function () use ($request, $stockAdjustment, $status, $branchId) {
                // 2. Update Master Dokumen
                $stockAdjustment->update([
                    'tgl_sa'          => $request->tgl_sa,
                    'status'          => $status,
                    'catatan'         => $request->catatan,
                    'tgl_jam_selesai' => $status === 'closed' ? now() : null,
                ]);

                // 3. Hapus detail lama
                $stockAdjustment->details()->delete();

                // 4. Insert Detail Baru & Eksekusi Potong Stok di product_stocks
                foreach ($request->product_id as $index => $productId) {
                    $qtyAdjust = (int) $request->qty[$index];
                    $itemNotes = $request->notes[$index] ?? null;

                    // Ambil stok komputer dari tabel product_stocks sesuai cabang dokumen
                    $pStock = DB::table('product_stocks')
                        ->where('branch_id', $branchId)
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();

                    // Pastikan default 0 jika belum ada catatan stok, jangan biarkan NULL
                    $stockBefore = $pStock ? (int) $pStock->stock : 0;
                    $stockAfter  = $stockBefore - $qtyAdjust;

                    StockAdjustmentDetail::create([
                        'stock_adjustment_id' => $stockAdjustment->id,
                        'product_id'          => $productId,
                        'stock_system'        => $stockBefore,
                        'qty'                 => $qtyAdjust,
                        'notes'               => $itemNotes,
                    ]);

                    // Jika status closed (Posting F10), potong stok cabang & catat riwayat
                    if ($status === 'closed') {
                        if ($pStock) {
                            DB::table('product_stocks')
                                ->where('id', $pStock->id)
                                ->update([
                                    'stock'      => $stockAfter,
                                    'updated_at' => now(),
                                ]);
                        } else {
                            DB::table('product_stocks')->insert([
                                'branch_id'  => $branchId,
                                'product_id' => $productId,
                                'stock'      => $stockAfter,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        DB::table('stock_movements')->insert([
                            'branch_id'    => $branchId,
                            'product_id'   => $productId,
                            'type'         => 'Stock Adjustment',
                            'qty'          => -$qtyAdjust,
                            'stock_before' => $stockBefore,
                            'stock_after'  => $stockAfter,
                            'reference_no' => $stockAdjustment->nomor_sa,
                            'notes'        => $itemNotes ?? 'Adjustment barang rusak/expired (' . $stockAdjustment->nomor_sa . ')',
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }
                }
            });

            $message = $status === 'closed' 
                ? 'Stock Adjustment berhasil diposting!' 
                : 'Perubahan Draft berhasil disimpan.';

            // Response JSON untuk request AJAX (F10)
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                ]);
            }

            return redirect()->route('stock-adjustments.index')->with('success', $message);

        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memperbarui data: ' . $e->getMessage()
                ], 500);
            }

            return back()->withInput()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }
    
    //print boloo 
    public function printPdf(StockAdjustment $stockAdjustment)
    {
        // Muat detail item beserta relasi produknya
        $stockAdjustment->load('details.product', 'user');
        
        // Return view khusus cetak PDF (silakan buat file blade ini di stock-adjustments/pdf.blade.php)
        return view('stock-adjustments.pdf', compact('stockAdjustment'));
    }
}