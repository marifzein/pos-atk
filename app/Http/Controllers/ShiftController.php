<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;
use App\Models\CashExpense;
use App\Models\Transaction; // 💡 Pastikan model Transaction di-import di sini
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShiftController extends Controller
{
    // 1. TAMPILKAN FORM ISI MODAL AWAL
    public function showOpenForm(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        $activeShift = Shift::where('user_id', Auth::id())
                            ->where('status', 'open')
                            ->exists();

        // dicek dulu apakah mobile atau desktop
        $isMobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent) 
                || preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', $userAgent);

           
        
        if ($activeShift) {
            
             // Jika lewat HP/Tablet, lempar langsung ke POS Mobile
            if ($isMobile) {
                return redirect()->route('kasir.mobile');
            }
            else{
                return redirect('/kasir');
            }
            
        }

        return view('kasir.open-shift');
    }

    // 2. SIMPAN MODAL AWAL KE DATABASE
    public function storeOpenShift(Request $request)
    {
        $userAgent = $request->header('User-Agent');
        // dicek dulu apakah mobile atau desktop
        $isMobile = preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i', $userAgent) 
                || preg_match('/(ipad|tablet|(android(?!.*mobile)))/i', $userAgent);

        $request->validate([
            'starting_cash' => 'required|numeric|min:0',
        ], [
            'starting_cash.required' => 'Uang modal awal wajib diisi!',
            'starting_cash.numeric' => 'Format harus berupa angka!',
            'starting_cash.min' => 'Uang modal tidak boleh minus!',
        ]);

        $activeShift = Shift::where('user_id', Auth::id())
                            ->where('status', 'open')
                            ->exists();

        if ($activeShift) {

            // Jika lewat HP/Tablet, lempar langsung ke POS Mobile
            if ($isMobile) {
                return redirect()->route('kasir.mobile');
            }
            else{
                return redirect('/kasir');
            }
            
        }

        Shift::create([
            'user_id' => Auth::id(),
            'starting_cash' => $request->starting_cash,
            'total_cash_sales' => 0,
            'operational_expense' => 0,
            'expected_cash' => $request->starting_cash,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        if ($isMobile) {
            return redirect('/kasir/mobile')->with('success', 'Shift berhasil dibuka. Selamat bertugas!');
        }
        else{
            return redirect('/kasir')->with('success', 'Shift berhasil dibuka. Selamat bertugas!');

        }
        
    }

    // 3. TAMPILKAN HALAMAN TUTUP SHIFT (KALKULASI SEBELUM CLOSING)
    public function showCloseForm()
    {
        $activeShift = Shift::where('user_id', Auth::id())
                            ->where('status', 'open')
                            ->first();

        if (!$activeShift) {
            return redirect('/kasir')->with('error', 'Tidak ada shift aktif yang perlu ditutup.');
        }

        // Gunakan helper yang sudah mencakup uang SP (DP/Lunas di depan) + Nota
        $calc = $this->calculateShiftCash($activeShift->id, $activeShift->starting_cash);

        $netCashSales = $calc['total_cash_inflow'];
        $expectedCash = $calc['expected_cash'];
        $totalExpense = $calc['total_expense'];

        return view('kasir.close-shift', compact('activeShift', 'netCashSales', 'expectedCash', 'totalExpense'));
    }

    // 4. PROSES TUTUP SHIFT & SIMPAN PERBEDAAN (VARIANCE) KE DB
    public function storeCloseShift(Request $request)
    {
        $request->validate([
            'ending_cash_actual' => 'required|numeric|min:0',
            'variance_reason' => 'nullable|string|max:500',
        ]);

        $activeShift = Shift::where('user_id', Auth::id())
                            ->where('status', 'open')
                            ->first();

        if (!$activeShift) {
            return redirect('/kasir')->with('error', 'Shift tidak ditemukan.');
        }

        // Gunakan helper yang akurat
        $calc = $this->calculateShiftCash($activeShift->id, $activeShift->starting_cash);

        $endingCashActual = (float) $request->ending_cash_actual;
        $variance = $endingCashActual - $calc['expected_cash'];

        $activeShift->update([
            'total_cash_sales'    => $calc['total_cash_inflow'],
            'operational_expense' => $calc['total_expense'],
            'expected_cash'       => $calc['expected_cash'],
            'ending_cash_actual'  => $endingCashActual,
            'variance'            => $variance,
            'variance_reason'     => $request->variance_reason,
            'status'              => 'closed',
            'closed_at'           => now(),
        ]);

        return redirect('/kasir/open-shift')->with('success', 'Shift berhasil ditutup! Laporan shift telah disimpan.');
    }

    // =========================================================================
    // 💡 METHOD BARU: KHUSUS UNTUK MONITORING & LAPORAN SHIFT (Z-REPORT)
    // =========================================================================

    // Tampilkan semua riwayat shift yang pernah dibuat
    // 5. RIWAYAT SESI & LAPORAN SHIFT
    // 5. RIWAYAT SESI & LAPORAN SHIFT
        public function index()
        {
            $shifts = Shift::with(['user', 'branch'])
                        ->latest('opened_at')
                        ->paginate(10);

            // Hitung ekspektasi sistem real-time untuk shift yang masih OPEN
            foreach ($shifts as $shift) {
                if ($shift->status === 'open') {
                    $calc = $this->calculateShiftCash($shift->id, $shift->starting_cash);
                    $shift->expected_cash = $calc['expected_cash'];
                }
            }

            return view('laporan.shift.index', compact('shifts'));
        }

    // Tampilkan rincian detail satu shift (Z-Report Lengkap)
    public function showlawas($id)
    {
        $shift = \App\Models\Shift::with('user')->findOrFail($id);

        // Agregasi Non-Cash (Card & Voucher) serta Total Omzet dari tabel transactions
        $summary = \App\Models\Transaction::where('shift_id', $shift->id)
            ->where('status', 'LUNAS')
            ->selectRaw('SUM(card) as total_card, SUM(voucher) as total_voucher, SUM(grand_total) as total_grand, COUNT(id) as count_trx')
            ->first();

        // Hitung total quantity produk yang terjual khusus di shift ini
        $totalQtySold = \Illuminate\Support\Facades\DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.shift_id', $shift->id)
            ->where('transactions.status', 'LUNAS')
            ->sum('transaction_details.qty');

        return view('laporan.shift.show', compact('shift', 'summary', 'totalQtySold'));
    }

    // 6. DETAIL Z-REPORT LENGKAP
    public function show($id)
    {
        $shift = Shift::with(['user', 'branch'])->findOrFail($id);

        // Jika shift masih open, hitung secara live real-time
        if ($shift->status === 'open') {
            $calc = $this->calculateShiftCash($shift->id, $shift->starting_cash);
            $shift->total_cash_sales = $calc['total_cash_inflow'];
            $shift->operational_expense = $calc['total_expense'];
            $shift->expected_cash = $calc['expected_cash'];
        }

        // Agregasi Non-Cash (Card & Voucher) dari Nota Transaksi (NP)
        $summary = Transaction::where('shift_id', $shift->id)
            ->where('status', '!=', 'BATAL')
            ->selectRaw('SUM(card) as total_card, SUM(voucher) as total_voucher, SUM(grand_total) as total_grand, COUNT(id) as count_trx')
            ->first();

        // Tambahkan pembayaran Non-Tunai saat pembuatan SP (baik DP maupun lunas awal via Card/QRIS/TF)
        $spNonCash = (float) DB::table('order_payments')
            ->where('shift_id', $shift->id)
            ->where(function ($q) {
                $q->where('payment_type', 'DP')
                  ->orWhere('no_bukti_bayar', 'LIKE', 'SP-%');
            })
            ->whereIn('metode_pembayaran', ['card', 'qris', 'transfer'])
            ->sum('nominal');

        $totalCard = ($summary->total_card ?? 0) + $spNonCash;

        // Hitung total quantity produk yang terjual di shift ini
        $totalQtySold = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.shift_id', $shift->id)
            ->where('transactions.status', '!=', 'BATAL')
            ->sum('transaction_details.qty');

        return view('laporan.shift.show', compact('shift', 'summary', 'totalCard', 'totalQtySold'));
    }

    // Helper untuk menghitung rekonsiliasi kas laci shift secara akurat
    private function calculateShiftCash($shiftId, $startingCash)
    {
        // 1. Uang masuk di kasir saat pembuatan SP (baik DP sebagian maupun Lunas 100% di awal)
        $totalKasMasukSP = (float) DB::table('order_payments')
            ->where('shift_id', $shiftId)
            ->where('metode_pembayaran', 'cash')
            ->where(function ($q) {
                $q->where('payment_type', 'DP')
                  ->orWhere('no_bukti_bayar', 'LIKE', 'SP-%');
            })
            ->sum('nominal');

        // 2. Uang masuk di kasir saat pelunasan / transaksi nota (NP) langsung
        $netCashSales = (float) Transaction::where('shift_id', $shiftId)
            ->where('status', '!=', 'BATAL')
            ->where('cash', '>', 0)
            ->selectRaw('SUM(GREATEST(0, cash - kembalian)) as total_tunai')
            ->value('total_tunai');

        // Total seluruh uang cash masuk dari penjualan/order
        $totalCashInflow = $totalKasMasukSP + $netCashSales;

        // 3. Total Beban Kas Keluar dari Laci
        $totalExpense = (float) CashExpense::where('shift_id', $shiftId)->sum('nominal');

        // 4. Saldo Kas Laci yang Seharusnya Ada
        $expectedCash = ((float) $startingCash + $totalCashInflow) - $totalExpense;

        return [
            'total_sp'          => $totalKasMasukSP,
            'net_cash_sales'    => $netCashSales,
            'total_cash_inflow' => $totalCashInflow,
            'total_expense'     => $totalExpense,
            'expected_cash'     => $expectedCash,
        ];
    }
}