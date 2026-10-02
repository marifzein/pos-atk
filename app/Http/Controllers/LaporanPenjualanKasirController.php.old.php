<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;
use App\Models\Branch;

class LaporanPenjualanKasirController extends Controller
{
   
    public function index(Request $request)
    {
        $user = Auth::user();

        $dari_tanggal = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->toDateString());
        $sampai_tanggal = $request->get('sampai_tanggal', Carbon::now()->toDateString());
        $kasir = $request->get('kasir');

        $branches = Branch::where('is_active', 1)->get();
        $selectedBranchId = $request->get('branch_id');
        if (!in_array(strtolower($user->role), ['owner', 'admin', 'developer']) && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
        }

        // Sumber 1: Transaksi Langsung POS (Non-SP / Ritel Langsung Lunas)
       $posDirect = DB::table('transactions')
            ->join('users', 'transactions.cashier_id', '=', 'users.id')
            ->leftJoin('branches', 'transactions.branch_id', '=', 'branches.id')
            ->where('transactions.status', 'LUNAS')
            // Kecualikan transaksi yang uang pelunasannya SUDAH masuk ke order_payments
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('order_payments')
                    ->whereRaw('order_payments.no_bukti_bayar = transactions.no_nota');
            })
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(transactions.created_at) as tanggal'),
                'transactions.branch_id',
                'branches.name as nama_cabang',
                'transactions.cashier_id',
                'users.name as nama_kasir',
                'transactions.id as ref_id',
                DB::raw('(transactions.cash - transactions.kembalian) as cash_in'),
                'transactions.card as card_in',
                'transactions.voucher as voucher_in'
            );

        // Sumber 2: Semua Penerimaan dari DP & Pelunasan SP
        $orderPayments = DB::table('order_payments')
            ->join('users', 'order_payments.cashier_id', '=', 'users.id')
            ->leftJoin('branches', 'order_payments.branch_id', '=', 'branches.id')
            ->whereBetween(DB::raw('DATE(order_payments.created_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(order_payments.created_at) as tanggal'),
                'order_payments.branch_id',
                'branches.name as nama_cabang',
                'order_payments.cashier_id',
                'users.name as nama_kasir',
                'order_payments.id as ref_id',
                DB::raw("CASE WHEN order_payments.metode_pembayaran = 'cash' THEN order_payments.nominal ELSE 0 END as cash_in"),
                DB::raw("CASE WHEN order_payments.metode_pembayaran IN ('card', 'qris', 'transfer') THEN order_payments.nominal ELSE 0 END as card_in"),
                DB::raw("0 as voucher_in")
            );

        // Filter Cabang pada masing-masing sub-query
        if ($selectedBranchId) {
            $posDirect->where('transactions.branch_id', $selectedBranchId);
            $orderPayments->where('order_payments.branch_id', $selectedBranchId);
        }

        // Filter Kasir
        if (!empty($kasir)) {
            $posDirect->where('users.name', 'LIKE', '%' . $kasir . '%');
            $orderPayments->where('users.name', 'LIKE', '%' . $kasir . '%');
        }

        // Batasan Role Kasir (Hanya lihat datanya sendiri)
        if (strtolower($user->role) === 'kasir') {
            $posDirect->where('transactions.cashier_id', $user->id);
            $orderPayments->where('order_payments.cashier_id', $user->id);
        }

        // Gabungkan kedua sumber data
        $unionQuery = $posDirect->unionAll($orderPayments);

        // Agregasi Laporan per Hari, Cabang, dan Kasir
        $query = DB::query()->fromSub($unionQuery, 'aliran_kas')
            ->select(
                'tanggal',
                'branch_id',
                'nama_cabang',
                'cashier_id',
                'nama_kasir',
                DB::raw('COUNT(ref_id) as jumlah_transaksi'),
                DB::raw('SUM(cash_in) as total_cash'),
                DB::raw('SUM(card_in) as total_card'),
                DB::raw('SUM(voucher_in) as total_voucher'),
                DB::raw('SUM(cash_in + card_in + voucher_in) as total_grand')
            )
            ->groupBy('tanggal', 'branch_id', 'nama_cabang', 'cashier_id', 'nama_kasir')
            ->orderBy('tanggal', 'asc')
            ->orderBy('nama_kasir', 'asc');

        // Agregasi Total Footer Periode
        $totals = DB::query()->fromSub($unionQuery, 'total_aliran')
            ->select(
                DB::raw('COUNT(ref_id) as total_transaksi'),
                DB::raw('SUM(cash_in) as total_cash'),
                DB::raw('SUM(card_in) as total_card'),
                DB::raw('SUM(voucher_in) as total_voucher'),
                DB::raw('SUM(cash_in + card_in + voucher_in) as total_grand')
            )->first();

        $reports = $query->paginate(20)->withQueryString();

        return view('laporan.penjualan-kasir', compact(
            'reports', 
            'totals', 
            'dari_tanggal', 
            'sampai_tanggal', 
            'kasir', 
            'branches', 
            'selectedBranchId'
        ));
    }

    /**
     * Export Data Penerimaan Kasir ke File Excel/CSV
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();

        $dari_tanggal = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->toDateString());
        $sampai_tanggal = $request->get('sampai_tanggal', Carbon::now()->toDateString());
        $kasir = $request->get('kasir');

        $selectedBranchId = $request->get('branch_id');
        if (!in_array(strtolower($user->role), ['owner', 'admin', 'developer']) && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
        }

        // Sumber 1: Transaksi Langsung POS (Ritel Langsung Lunas)
        $posDirect = DB::table('transactions')
            ->join('users', 'transactions.cashier_id', '=', 'users.id')
            ->leftJoin('branches', 'transactions.branch_id', '=', 'branches.id')
            ->whereNull('transactions.order_id')
            ->where('transactions.status', 'LUNAS')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(transactions.created_at) as tanggal'),
                'transactions.branch_id',
                'branches.name as nama_cabang',
                'transactions.cashier_id',
                'users.name as nama_kasir',
                'transactions.id as ref_id',
                DB::raw('(transactions.cash - transactions.kembalian) as cash_in'),
                'transactions.card as card_in',
                'transactions.voucher as voucher_in'
            );

        // Sumber 2: Semua Penerimaan dari DP & Pelunasan SP
        $orderPayments = DB::table('order_payments')
            ->join('users', 'order_payments.cashier_id', '=', 'users.id')
            ->leftJoin('branches', 'order_payments.branch_id', '=', 'branches.id')
            ->whereBetween(DB::raw('DATE(order_payments.created_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(order_payments.created_at) as tanggal'),
                'order_payments.branch_id',
                'branches.name as nama_cabang',
                'order_payments.cashier_id',
                'users.name as nama_kasir',
                'order_payments.id as ref_id',
                DB::raw("CASE WHEN order_payments.metode_pembayaran = 'cash' THEN order_payments.nominal ELSE 0 END as cash_in"),
                DB::raw("CASE WHEN order_payments.metode_pembayaran IN ('card', 'qris', 'transfer') THEN order_payments.nominal ELSE 0 END as card_in"),
                DB::raw("0 as voucher_in")
            );

        // Filter Cabang
        if ($selectedBranchId) {
            $posDirect->where('transactions.branch_id', $selectedBranchId);
            $orderPayments->where('order_payments.branch_id', $selectedBranchId);
        }

        // Filter Nama Kasir
        if (!empty($kasir)) {
            $posDirect->where('users.name', 'LIKE', '%' . $kasir . '%');
            $orderPayments->where('users.name', 'LIKE', '%' . $kasir . '%');
        }

        // Filter Role Kasir
        if (strtolower($user->role) === 'kasir') {
            $posDirect->where('transactions.cashier_id', $user->id);
            $orderPayments->where('order_payments.cashier_id', $user->id);
        }

        $unionQuery = $posDirect->unionAll($orderPayments);

        $reports = DB::query()->fromSub($unionQuery, 'aliran_kas')
            ->select(
                'tanggal',
                'nama_cabang',
                'nama_kasir',
                DB::raw('COUNT(ref_id) as jumlah_transaksi'),
                DB::raw('SUM(cash_in) as total_cash'),
                DB::raw('SUM(card_in) as total_card'),
                DB::raw('SUM(voucher_in) as total_voucher'),
                DB::raw('SUM(cash_in + card_in + voucher_in) as total_grand')
            )
            ->groupBy('tanggal', 'branch_id', 'nama_cabang', 'cashier_id', 'nama_kasir')
            ->orderBy('tanggal', 'asc')
            ->orderBy('nama_kasir', 'asc')
            ->get();

        $filename = "Laporan_Penerimaan_Kas_{$dari_tanggal}_sd_{$sampai_tanggal}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($reports) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM agar Excel membaca karakter & angka secara rapi
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Baris Header Kolom
            fputcsv($file, [
                'No', 
                'Tanggal', 
                'Cabang', 
                'Nama Kasir', 
                'Jml Transaksi', 
                'Uang Cash (Rp)', 
                'Card/QRIS/TF (Rp)', 
                'Voucher (Rp)', 
                'Total Masuk (Rp)'
            ]);

            $no = 1;
            $sumCash = 0;
            $sumCard = 0;
            $sumVoucher = 0;
            $sumGrand = 0;

            foreach ($reports as $row) {
                $sumCash += $row->total_cash;
                $sumCard += $row->total_card;
                $sumVoucher += $row->total_voucher;
                $sumGrand += $row->total_grand;

                fputcsv($file, [
                    $no++,
                    $row->tanggal,
                    $row->nama_cabang ?? '-',
                    $row->nama_kasir,
                    $row->jumlah_transaksi,
                    $row->total_cash,
                    $row->total_card,
                    $row->total_voucher,
                    $row->total_grand
                ]);
            }

            // Baris Total di bagian akhir
            fputcsv($file, [
                '', 
                'TOTAL', 
                '', 
                '', 
                '', 
                $sumCash, 
                $sumCard, 
                $sumVoucher, 
                $sumGrand
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}