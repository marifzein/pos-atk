<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\DocumentNumber;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Auth;

class StockOpnameController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSpv = strtolower($user->role ?? '') === 'spv';

        // 1. Tentukan list cabang sesuai role user
        if ($isSpv) {
            $branches = DB::table('branches')->where('id', $user->branch_id)->where('is_active', 1)->get();
            $selectedBranchId = $user->branch_id;
        } else {
            $branches = DB::table('branches')->where('is_active', 1)->get();
            $selectedBranchId = $request->branch_id;
        }

        // 2. Query data Stock Opname
        $query = StockOpname::with('branch')->withCount('details');

        if ($selectedBranchId) {
            $query->where('branch_id', $selectedBranchId);
        }

        $opnames = $query->latest()->paginate(10)->withQueryString();

        return view('stock-opname.index', compact('opnames', 'branches', 'selectedBranchId', 'isSpv'));
    }

    public function start(Request $request)
    {
        $user = Auth::user();
        $isSpv = strtolower($user->role ?? '') === 'spv';

        // SPV otomatis cabangnya sendiri, role lain wajib memilih dari dropdown
        $branchId = $isSpv ? (int) $user->branch_id : (int) $request->branch_id;

        if (!$branchId) {
            return back()->with('warning', 'Pilih salah satu cabang terlebih dahulu sebelum memulai Stock Opname!');
        }

        // ATURAN KRUSIAL: Dalam 1 cabang HANYA BISA 1 Stock Opname aktif (OPEN)
        $openOpname = StockOpname::where('branch_id', $branchId)
            ->where('status', 'OPEN')
            ->first();

        if ($openOpname) {
            return redirect('/stock-opname/' . $openOpname->id)
                ->with('warning', 'Cabang ini masih memiliki Stock Opname berstatus OPEN. Selesaikan/Posting terlebih dahulu sebelum membuat yang baru.');
        }

        // Generate Nomor SO otomatis per Cabang menggunakan helper generate_custom
        $opnameNo = DocumentNumber::generate_custom(
            'stock_opnames',
            'opname_no',
            'SO',
            $branchId
        );

        $opname = StockOpname::create([
            'branch_id'   => $branchId,
            'opname_no'   => $opnameNo,
            'opname_date' => now(),
            'user_name'   => $user->name ?? 'Admin',
            'status'      => 'OPEN',
            'notes'       => null,
        ]);

        return redirect('/stock-opname/' . $opname->id)->with('success', 'Stock Opname baru berhasil dibuat.');
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load('branch');
        $details = $stockOpname->details()->with('product')->get();

        return view('stock-opname.show', compact('stockOpname', 'details'));
    }

    public function store(Request $request, StockOpname $stockOpname)
    {
        // Proteksi: Jika dokumen sudah di-posting, kunci total
        if ($stockOpname->status !== 'OPEN') {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen Stock Opname sudah diposting dan tidak dapat diubah.'
            ], 422);
        }

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'stok_fisik' => 'required|integer|min:0'
        ]);

        try {
            $detail = DB::transaction(function () use ($request, $stockOpname) {
                $product  = Product::findOrFail($request->product_id);
                $branchId = (int) $stockOpname->branch_id;
                $stokInputBaru = (int) $request->stok_fisik;

                // 1. Ambil Stok Komputer Terkini dari product_stocks cabang
                $pStock = DB::table('product_stocks')
                    ->where('branch_id', $branchId)
                    ->where('product_id', $product->id)
                    ->lockForUpdate()
                    ->first();

                $currentLiveStock = $pStock ? (int) $pStock->stock : 0;

                // 2. Cek apakah barang sudah ada di dokumen SO ini
                $existingDetail = StockOpnameDetail::where('stock_opname_id', $stockOpname->id)
                    ->where('product_id', $product->id)
                    ->first();

                if ($existingDetail) {
                    // KASUS A: Barang discan ulang (Akumulasi Fisik)
                    $stokSystemAsli = (int) $existingDetail->stock_system;
                    $stokFisikTotal = (int) $existingDetail->stock_physical + $stokInputBaru;
                    $totalSelisih   = $stokFisikTotal - $stokSystemAsli;

                    // Update detail dokumen SO
                    $existingDetail->update([
                        'stock_physical' => $stokFisikTotal,
                        'difference'     => $totalSelisih,
                        'notes'          => $request->notes ?? $existingDetail->notes
                    ]);

                    // 🔥 REAL-TIME UPDATE CABANG:
                    // Karena barang baru bertambah sejumlah $stokInputBaru, stok cabang langsung bertambah sebesar itu
                    $newLiveStock = $currentLiveStock + $stokInputBaru;

                    if ($pStock) {
                        DB::table('product_stocks')->where('id', $pStock->id)->update([
                            'stock'      => $newLiveStock,
                            'updated_at' => now(),
                        ]);
                    } else {
                        DB::table('product_stocks')->insert([
                            'branch_id'  => $branchId,
                            'product_id' => $product->id,
                            'stock'      => $newLiveStock,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Suntik mutasi ke kartu stok untuk tambahan scan ini
                    if ($stokInputBaru != 0) {
                        DB::table('stock_movements')->insert([
                            'branch_id'    => $branchId,
                            'product_id'   => $product->id,
                            'type'         => 'STOCK_OPNAME',
                            'qty'          => $stokInputBaru,
                            'stock_before' => $currentLiveStock,
                            'stock_after'  => $newLiveStock,
                            'reference_no' => $stockOpname->opname_no,
                            'notes'        => ($request->notes ? $request->notes . ' ' : '') . '(Akumulasi scan SO)',
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ]);
                    }

                    return [
                        'success'   => true,
                        'is_update' => true,
                        'message'   => 'Stok fisik berhasil diakumulasikan & stok toko langsung diperbarui!',
                        'detail'    => [
                            'sku'            => $product->sku,
                            'name'           => $product->name,
                            'stock_system'   => $stokSystemAsli,
                            'stock_physical' => $stokFisikTotal,
                            'difference'     => $totalSelisih
                        ]
                    ];
                }

                // KASUS B: Barang baru pertama kali discan di SO ini
                $selisih = $stokInputBaru - $currentLiveStock;

                StockOpnameDetail::create([
                    'stock_opname_id' => $stockOpname->id,
                    'product_id'      => $product->id,
                    'stock_system'    => $currentLiveStock,
                    'stock_physical'  => $stokInputBaru,
                    'difference'      => $selisih,
                    'notes'           => $request->notes
                ]);

                // 🔥 REAL-TIME UPDATE CABANG:
                // Samakan langsung stok di product_stocks cabang dengan stok fisik yang diinput
                if ($pStock) {
                    DB::table('product_stocks')->where('id', $pStock->id)->update([
                        'stock'      => $stokInputBaru,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('product_stocks')->insert([
                        'branch_id'  => $branchId,
                        'product_id' => $product->id,
                        'stock'      => $stokInputBaru,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Suntik mutasi selisih pertama ke kartu stok
                if ($selisih != 0) {
                    DB::table('stock_movements')->insert([
                        'branch_id'    => $branchId,
                        'product_id'   => $product->id,
                        'type'         => 'STOCK_OPNAME',
                        'qty'          => $selisih,
                        'stock_before' => $currentLiveStock,
                        'stock_after'  => $stokInputBaru,
                        'reference_no' => $stockOpname->opname_no,
                        'notes'        => $request->notes ?? 'Penyesuaian Fisik Opname (' . $stockOpname->opname_no . ')',
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                return [
                    'success'   => true,
                    'is_update' => false,
                    'message'   => 'Stok fisik dicatat & stok toko langsung disinkronkan!',
                    'detail'    => [
                        'sku'            => $product->sku,
                        'name'           => $product->name,
                        'stock_system'   => $currentLiveStock,
                        'stock_physical' => $stokInputBaru,
                        'difference'     => $selisih
                    ]
                ];
            });

            return response()->json($detail);

        } catch (\Throwable $e) {
            Log::error('Error Realtime SO Store: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function finish(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'OPEN') {
            return redirect('/stock-opname')->with('warning', 'Stock Opname ini sudah diposting sebelumnya.');
        }

        // Karena stok sudah real-time terpotong/bertambah saat scan,
        // di sini kita HANYA MENGUNCI dokumen saja!
        $stockOpname->update([
            'status'      => 'POSTED',
            'finished_at' => now(),
        ]);

        return redirect('/stock-opname')->with('success', 'Stock Opname resmi diposting dan dokumen telah dikunci permanen!');
    }

    public function print(StockOpname $stockOpname)
    {
        $stockOpname->load('branch');
        $details = $stockOpname->details()->with('product')->get();
        $setting = DB::table('settings')->first();

        return view('stock-opname.print', compact('stockOpname', 'details', 'setting'));
    }

    // pencegatan SO aktip
    public function checkActive(Request $request)   
    {
        $branchId = (int) $request->branch_id;

        if (!$branchId) {
            return response()->json(['has_open' => false]);
        }

        $openOpname = StockOpname::where('branch_id', $branchId)
            ->where('status', 'OPEN')
            ->first();

        if ($openOpname) {
            return response()->json([
                'has_open'   => true,
                'opname_id'  => $openOpname->id,
                'opname_no'  => $openOpname->opname_no,
            ]);
        }

        return response()->json(['has_open' => false]);
    }
}