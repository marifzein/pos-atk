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
            return back()->with('error', 'Shift kasir belum dibuka! Buka shift terlebih dahulu.');
        }

        // Format No Bukti: KK-[KODE_CABANG]-[YYMMDD]-[INDEX]
        $branchCode = $user->branch->kode_cabang ?? 'CB';
        $todayCode = date('ymd');
        $countToday = CashExpense::whereDate('created_at', today())->count() + 1;
        $noBukti = sprintf('KK-%s-%s-%04d', $branchCode, $todayCode, $countToday);

        DB::transaction(function () use ($request, $user, $activeShift, $noBukti) {
            CashExpense::create([
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

        return back()->with('success', 'Pengeluaran kas berhasil dicatat!');
    }

    public function destroy($id)
    {
        $expense = CashExpense::findOrFail($id);
        $expense->delete();
        return back()->with('success', 'Catatan pengeluaran berhasil dihapus!');
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
                    'supplier_name'  => $pb->supplier->nama ?? 'Supplier Umum',
                    'ref_doc'        => $pb->no_dokumen_supplier ?? $pb->no_po ?? '-',
                    'total_tagihan'  => $totalTagihan,
                    'sisa_tagihan'   => $sisa,
                ];
            })
            ->filter(fn($item) => $item['sisa_tagihan'] > 0)
            ->values();

        return response()->json($unpaid);
    }
}