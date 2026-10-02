<?php

namespace App\Http\Controllers\Acc;

use App\Http\Controllers\Controller; // Wajib import base controller
use App\Models\CashExpense;
use App\Models\PenerimaanBarang;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashExpenseController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $activeShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        $expenses = collect([]);
        
        if ($activeShift) {
            // Cukup ambil daftar transaksi pengeluaran shift aktif
            $expenses = CashExpense::with('user')
                ->where('shift_id', $activeShift->id)
                ->latest()
                ->get();
        }

        return view('kasir.pengeluaran.index', compact('activeShift', 'expenses'));
    }

    

    

    // Endpoint JSON untuk modal/dropdown pencarian dokumen penerimaan barang belum lunas
    public function getUnpaidPenerimaan()
    {
        $branchId = auth()->user()->branch_id ?? 1;

        $unpaid = PenerimaanBarang::with(['supplier', 'items'])
            ->where('branch_id', $branchId)
            ->latest()
            ->get()
            ->map(function ($pb) {
                $totalTagihan = (float) $pb->items->sum('subtotal');
                $terbayar = (float) CashExpense::where('reference_type', 'penerimaan_barang')
                    ->where('reference_id', $pb->id)
                    ->sum('nominal');
                $sisa = max(0, $totalTagihan - $terbayar);

                return [
                    'id'             => $pb->id,
                    'no_penerimaan'  => $pb->no_penerimaan,
                    'supplier_name'  => $pb->supplier->name ?? 'Supplier Umum',
                    'ref_doc'        => $pb->no_dokumen_supplier ?? $pb->no_po ?? '-',
                    'total_tagihan'  => $totalTagihan,
                    'sisa_tagihan'   => $sisa,
                ];
            })
            ->filter(fn($item) => $item['sisa_tagihan'] > 0)
            ->values();

        return response()->json($unpaid);
    }

    public function storelawas(Request $request)
    {
        $request->validate([
            'kategori' => 'required|in:operasional,teknisi_subkon,barang_supplier,tarik_owner,lain_lain',
            'penerima' => 'required|string|max:100',
            'nominal'  => 'required|numeric|min:1',
            'catatan'  => 'nullable|string',
            'penerimaan_barang_id' => 'nullable|required_if:kategori,barang_supplier|exists:penerimaan_barang,id',
        ]);

        $user = auth()->user();
        $activeShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift kasir belum dibuka! Buka shift terlebih dahulu.'
            ], 422);
        }

        $branchCode = $user->branch->kode_cabang ?? 'CB';
        $todayCode = date('ymd');
        $countToday = CashExpense::whereDate('created_at', today())->count() + 1;
        $noBukti = sprintf('KK-%s-%s-%04d', $branchCode, $todayCode, $countToday);

        $expense = DB::transaction(function () use ($request, $user, $activeShift, $noBukti) {
            return CashExpense::create([
                'branch_id'      => $user->branch_id ?? 1,
                'shift_id'       => $activeShift->id,
                'user_id'        => $user->id,
                'no_bukti'       => $noBukti,
                'kategori'       => $request->kategori,
                'penerima'       => $request->penerima,
                'nominal'        => $request->nominal,
                'reference_type' => $request->kategori === 'barang_supplier' ? 'penerimaan_barang' : null,
                'reference_id'   => $request->kategori === 'barang_supplier' ? $request->penerimaan_barang_id : null,
                'catatan'        => $request->catatan,
            ]);
        });

        return response()->json([
            'success'   => true,
            'message'   => 'Pengeluaran kas berhasil dicatat!',
            'print_url' => route('kasir.pengeluaran.print', $expense->id)
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori' => 'required|in:operasional,teknisi_subkon,barang_supplier,tarik_owner,lain_lain',
            'penerima' => 'required|string|max:100',
            'nominal'  => 'required|numeric|min:1',
            'catatan'  => 'nullable|string',
            'penerimaan_barang_id' => 'nullable|required_if:kategori,barang_supplier|exists:penerimaan_barang,id',
        ]);

        $user = auth()->user();
        $activeShift = Shift::where('user_id', $user->id)
            ->where('status', 'open')
            ->latest()
            ->first();

        if (!$activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift kasir belum dibuka! Buka shift terlebih dahulu.'
            ], 422);
        }

        // 1. MODAL AWAL SHIFT
        $modalAwal = (float) ($activeShift->starting_cash ?? $activeShift->modal_awal ?? 0);

        // 2. UANG MASUK DARI DP PESANAN KASIR (Hanya payment_type = 'DP' dan metode 'cash')
        $totalDpMasuk = (float) DB::table('order_payments')
            ->where('shift_id', $activeShift->id)
            ->where('payment_type', 'DP')
            ->where('metode_pembayaran', 'cash')
            ->sum('nominal');

        // 3. UANG MASUK BERSIH DARI TRANSAKSI NOTA PENJUALAN (NP) PADA SHIFT INI
        // Menghitung pelunasan sisa saat ambil barang maupun penjualan langsung dari staf (cash - kembalian)
        $totalTransaksiCash = (float) DB::table('transactions')
            ->where('shift_id', $activeShift->id)
            ->where('status', '!=', 'BATAL')
            ->where('cash', '>', 0)
            ->selectRaw('SUM(cash - kembalian) as total_tunai')
            ->value('total_tunai');

        // TOTAL UANG KAS MASUK KE LACI
        $totalKasMasuk = $modalAwal + $totalDpMasuk + $totalTransaksiCash;

        // 4. TOTAL PENGELUARAN KAS KELUAR PADA SHIFT INI
        $totalKasKeluar = (float) CashExpense::where('shift_id', $activeShift->id)->sum('nominal');

        // 5. SALDO RIIL FISIK UANG DI LACI KASIR SAAT INI
        $saldoKasTersedia = $totalKasMasuk - $totalKasKeluar;

        // 6. VALIDASI BLIND AUDIT: TOLAK JIKA PENGELUARAN MELEBIHI KAS DI LACI
        if ($request->nominal > $saldoKasTersedia) {
            return response()->json([
                'success' => false,
                'message' => 'Nominal pengeluaran melebihi estimasi saldo kas tunai pada shift ini. Periksa kembali uang fisik atau konfirmasi ke Supervisor.'
            ], 422);
        }

        // 3. JIKA LOLOS, LANJUTKAN SIMPAN DATA
        $branchCode = $user->branch->kode_cabang ?? 'CB';
        $todayCode = date('ymd');
        $countToday = CashExpense::whereDate('created_at', today())->count() + 1;
        $noBukti = sprintf('KK-%s-%s-%04d', $branchCode, $todayCode, $countToday);

        $expense = DB::transaction(function () use ($request, $user, $activeShift, $noBukti) {
            return CashExpense::create([
                'branch_id'      => $user->branch_id ?? 1,
                'shift_id'       => $activeShift->id,
                'user_id'        => $user->id,
                'no_bukti'       => $noBukti,
                'kategori'       => $request->kategori,
                'penerima'       => $request->penerima,
                'nominal'        => $request->nominal,
                'reference_type' => $request->kategori === 'barang_supplier' ? 'penerimaan_barang' : null,
                'reference_id'   => $request->kategori === 'barang_supplier' ? $request->penerimaan_barang_id : null,
                'catatan'        => $request->catatan,
            ]);
        });

        return response()->json([
            'success'   => true,
            'message'   => 'Pengeluaran kas berhasil dicatat!',
            'print_url' => route('kasir.pengeluaran.print', $expense->id)
        ]);
    }   

    // Tampilan Cetak Struk 58mm Kas Keluar
    public function print($id)
    {
        $expense = CashExpense::with(['branch', 'user', 'shift'])->findOrFail($id);

        $shopSetting = \App\Models\Setting::first() ?? new \App\Models\Setting([
            'nama_toko'   => 'CAHAYA BUSUR GROUP',
            'alamat'      => 'Jl. Teuku Umar No. 67, Kadipaten - Bojonegoro',
            'telepon'     => '087627125',
            'footer_nota' => 'Harap simpan struk ini sebagai bukti kas keluar yang sah'
        ]);

        return view('kasir.pengeluaran.print', compact('expense', 'shopSetting'));
    }
}