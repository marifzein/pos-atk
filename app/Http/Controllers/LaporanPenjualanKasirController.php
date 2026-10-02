<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 
use Carbon\Carbon;
use App\Models\Branch;

class LaporanPenjualanKasirController extends Controller
{
    private function buildUnionQuerylawasss($dari_tanggal, $sampai_tanggal, $selectedBranchId, $kasir, $user)
    {
        // 1. Modal Awal Shift Kasir (starting_cash) - 11 Kolom
        $shiftsQuery = DB::table('shifts')
            ->join('users', 'shifts.user_id', '=', 'users.id')
            ->leftJoin('branches', 'shifts.branch_id', '=', 'branches.id')
            ->whereBetween(DB::raw('DATE(shifts.opened_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(shifts.opened_at) as tanggal'),
                'shifts.branch_id',
                'branches.name as nama_cabang',
                'shifts.user_id as cashier_id',
                'users.name as nama_kasir',
                'shifts.id as ref_id',
                'shifts.starting_cash as starting_cash',
                DB::raw('0 as cash_in'),
                DB::raw('0 as card_in'),
                DB::raw('0 as voucher_in'),
                DB::raw('0 as expense_out')
            );

        // 2. Transaksi Penjualan & Pelunasan Kasir (Nota Penjualan NP) - 11 Kolom
        $transactionsQuery = DB::table('transactions')
            ->join('users', 'transactions.cashier_id', '=', 'users.id')
            ->leftJoin('branches', 'transactions.branch_id', '=', 'branches.id')
            ->where('transactions.status', '!=', 'BATAL')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(transactions.created_at) as tanggal'),
                'transactions.branch_id',
                'branches.name as nama_cabang',
                'transactions.cashier_id',
                'users.name as nama_kasir',
                'transactions.id as ref_id',
                DB::raw('0 as starting_cash'),
                DB::raw('GREATEST(0, transactions.cash - transactions.kembalian) as cash_in'),
                'transactions.card as card_in',
                'transactions.voucher as voucher_in',
                DB::raw('0 as expense_out')
            );

        // 3. Penerimaan DP Pesanan Kasir (Surat Pesanan SP) - 11 Kolom
        $orderDpQuery = DB::table('order_payments')
            ->join('users', 'order_payments.cashier_id', '=', 'users.id')
            ->leftJoin('branches', 'order_payments.branch_id', '=', 'branches.id')
            ->where('order_payments.payment_type', 'DP')
            ->whereBetween(DB::raw('DATE(order_payments.created_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(order_payments.created_at) as tanggal'),
                'order_payments.branch_id',
                'branches.name as nama_cabang',
                'order_payments.cashier_id',
                'users.name as nama_kasir',
                'order_payments.id as ref_id',
                DB::raw('0 as starting_cash'),
                DB::raw("CASE WHEN order_payments.metode_pembayaran = 'cash' THEN order_payments.nominal ELSE 0 END as cash_in"),
                DB::raw("CASE WHEN order_payments.metode_pembayaran IN ('card', 'qris', 'transfer') THEN order_payments.nominal ELSE 0 END as card_in"),
                DB::raw('0 as voucher_in'),
                DB::raw('0 as expense_out')
            );

        // 4. Pengeluaran Kas Kasir (cash_expenses) - 11 Kolom
        $expenseQuery = DB::table('cash_expenses')
            ->join('users', 'cash_expenses.user_id', '=', 'users.id')
            ->leftJoin('branches', 'cash_expenses.branch_id', '=', 'branches.id')
            ->whereBetween(DB::raw('DATE(cash_expenses.created_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(cash_expenses.created_at) as tanggal'),
                'cash_expenses.branch_id',
                'branches.name as nama_cabang',
                'cash_expenses.user_id as cashier_id',
                'users.name as nama_kasir',
                'cash_expenses.id as ref_id',
                DB::raw('0 as starting_cash'),
                DB::raw('0 as cash_in'),
                DB::raw('0 as card_in'),
                DB::raw('0 as voucher_in'),
                'cash_expenses.nominal as expense_out'
            );

        // Filter Cabang
        if ($selectedBranchId) {
            $shiftsQuery->where('shifts.branch_id', $selectedBranchId);
            $transactionsQuery->where('transactions.branch_id', $selectedBranchId);
            $orderDpQuery->where('order_payments.branch_id', $selectedBranchId);
            $expenseQuery->where('cash_expenses.branch_id', $selectedBranchId);
        }

        // Filter Nama Kasir
        if (!empty($kasir)) {
            $shiftsQuery->where('users.name', 'LIKE', '%' . $kasir . '%');
            $transactionsQuery->where('users.name', 'LIKE', '%' . $kasir . '%');
            $orderDpQuery->where('users.name', 'LIKE', '%' . $kasir . '%');
            $expenseQuery->where('users.name', 'LIKE', '%' . $kasir . '%');
        }

        // Filter Role Kasir
        if (strtolower($user->role) === 'kasir') {
            $shiftsQuery->where('shifts.user_id', $user->id);
            $transactionsQuery->where('transactions.cashier_id', $user->id);
            $orderDpQuery->where('order_payments.cashier_id', $user->id);
            $expenseQuery->where('cash_expenses.user_id', $user->id);
        }

        return $shiftsQuery->unionAll($transactionsQuery)->unionAll($orderDpQuery)->unionAll($expenseQuery);
    }

    private function buildUnionQuery($dari_tanggal, $sampai_tanggal, $selectedBranchId, $kasir, $user)
    {
        // 1. Modal Awal Shift Kasir (starting_cash) - 11 Kolom
        $shiftsQuery = DB::table('shifts')
            ->join('users', 'shifts.user_id', '=', 'users.id')
            ->leftJoin('branches', 'shifts.branch_id', '=', 'branches.id')
            ->whereBetween(DB::raw('DATE(shifts.opened_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(shifts.opened_at) as tanggal'),
                'shifts.branch_id',
                'branches.name as nama_cabang',
                'shifts.user_id as cashier_id',
                'users.name as nama_kasir',
                'shifts.id as ref_id',
                'shifts.starting_cash as starting_cash',
                DB::raw('0 as cash_in'),
                DB::raw('0 as card_in'),
                DB::raw('0 as voucher_in'),
                DB::raw('0 as expense_out')
            );

        // 2. Transaksi Penjualan & Pelunasan Kasir (Nota Penjualan NP) - 11 Kolom
        $transactionsQuery = DB::table('transactions')
            ->join('users', 'transactions.cashier_id', '=', 'users.id')
            ->leftJoin('branches', 'transactions.branch_id', '=', 'branches.id')
            ->where('transactions.status', '!=', 'BATAL')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(transactions.created_at) as tanggal'),
                'transactions.branch_id',
                'branches.name as nama_cabang',
                'transactions.cashier_id',
                'users.name as nama_kasir',
                'transactions.id as ref_id',
                DB::raw('0 as starting_cash'),
                DB::raw('GREATEST(0, transactions.cash - transactions.kembalian) as cash_in'),
                'transactions.card as card_in',
                'transactions.voucher as voucher_in',
                DB::raw('0 as expense_out')
            );

        // 3. Penerimaan Uang Kasir dari SP (DP ataupun Langsung Lunas di Awal) - 11 Kolom
        $orderDpQuery = DB::table('order_payments')
            ->join('users', 'order_payments.cashier_id', '=', 'users.id')
            ->leftJoin('branches', 'order_payments.branch_id', '=', 'branches.id')
            ->where(function ($q) {
                $q->where('order_payments.payment_type', 'DP')
                  ->orWhere('order_payments.no_bukti_bayar', 'LIKE', 'SP-%');
            })
            ->whereBetween(DB::raw('DATE(order_payments.created_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(order_payments.created_at) as tanggal'),
                'order_payments.branch_id',
                'branches.name as nama_cabang',
                'order_payments.cashier_id',
                'users.name as nama_kasir',
                'order_payments.id as ref_id',
                DB::raw('0 as starting_cash'),
                DB::raw("CASE WHEN order_payments.metode_pembayaran = 'cash' THEN order_payments.nominal ELSE 0 END as cash_in"),
                DB::raw("CASE WHEN order_payments.metode_pembayaran IN ('card', 'qris', 'transfer') THEN order_payments.nominal ELSE 0 END as card_in"),
                DB::raw('0 as voucher_in'),
                DB::raw('0 as expense_out')
            );

        // 4. Pengeluaran Kas Kasir (cash_expenses) - 11 Kolom
        $expenseQuery = DB::table('cash_expenses')
            ->join('users', 'cash_expenses.user_id', '=', 'users.id')
            ->leftJoin('branches', 'cash_expenses.branch_id', '=', 'branches.id')
            ->whereBetween(DB::raw('DATE(cash_expenses.created_at)'), [$dari_tanggal, $sampai_tanggal])
            ->select(
                DB::raw('DATE(cash_expenses.created_at) as tanggal'),
                'cash_expenses.branch_id',
                'branches.name as nama_cabang',
                'cash_expenses.user_id as cashier_id',
                'users.name as nama_kasir',
                'cash_expenses.id as ref_id',
                DB::raw('0 as starting_cash'),
                DB::raw('0 as cash_in'),
                DB::raw('0 as card_in'),
                DB::raw('0 as voucher_in'),
                'cash_expenses.nominal as expense_out'
            );

        // Filter Cabang
        if ($selectedBranchId) {
            $shiftsQuery->where('shifts.branch_id', $selectedBranchId);
            $transactionsQuery->where('transactions.branch_id', $selectedBranchId);
            $orderDpQuery->where('order_payments.branch_id', $selectedBranchId);
            $expenseQuery->where('cash_expenses.branch_id', $selectedBranchId);
        }

        // Filter Nama Kasir
        if (!empty($kasir)) {
            $shiftsQuery->where('users.name', 'LIKE', '%' . $kasir . '%');
            $transactionsQuery->where('users.name', 'LIKE', '%' . $kasir . '%');
            $orderDpQuery->where('users.name', 'LIKE', '%' . $kasir . '%');
            $expenseQuery->where('users.name', 'LIKE', '%' . $kasir . '%');
        }

        // Filter Role Kasir
        if (strtolower($user->role) === 'kasir') {
            $shiftsQuery->where('shifts.user_id', $user->id);
            $transactionsQuery->where('transactions.cashier_id', $user->id);
            $orderDpQuery->where('order_payments.cashier_id', $user->id);
            $expenseQuery->where('cash_expenses.user_id', $user->id);
        }

        return $shiftsQuery->unionAll($transactionsQuery)->unionAll($orderDpQuery)->unionAll($expenseQuery);
    }

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

        $unionQuery = $this->buildUnionQuery($dari_tanggal, $sampai_tanggal, $selectedBranchId, $kasir, $user);

        // Agregasi Data per Hari, Cabang, dan Kasir
        $query = DB::query()->fromSub($unionQuery, 'aliran_kas')
            ->select(
                'tanggal',
                'branch_id',
                'nama_cabang',
                'cashier_id',
                'nama_kasir',
                DB::raw('COUNT(CASE WHEN expense_out = 0 AND starting_cash = 0 THEN ref_id END) as jumlah_transaksi'),
                DB::raw('SUM(starting_cash) as total_modal_awal'),
                DB::raw('SUM(cash_in) as total_cash_masuk'),
                DB::raw('SUM(expense_out) as total_expense'),
                // Saldo Kasir Fisik = (Modal Awal + Cash Masuk) - Pengeluaran
                DB::raw('(SUM(starting_cash) + SUM(cash_in)) - SUM(expense_out) as saldo_kasir'),
                DB::raw('SUM(card_in) as total_card'),
                DB::raw('SUM(voucher_in) as total_voucher'),
                // Total Setoran Fisik Kasir + Non-Tunai Bank
                DB::raw('((SUM(starting_cash) + SUM(cash_in)) - SUM(expense_out)) + SUM(card_in) as total_grand')
            )
            ->groupBy('tanggal', 'branch_id', 'nama_cabang', 'cashier_id', 'nama_kasir')
            ->orderBy('tanggal', 'desc')
            ->orderBy('nama_kasir', 'asc');

        // Total Periode Footer
        $totals = DB::query()->fromSub($unionQuery, 'total_aliran')
            ->select(
                DB::raw('COUNT(CASE WHEN expense_out = 0 AND starting_cash = 0 THEN ref_id END) as total_transaksi'),
                DB::raw('SUM(starting_cash) as total_modal_awal'),
                DB::raw('SUM(cash_in) as total_cash_masuk'),
                DB::raw('SUM(expense_out) as total_expense'),
                DB::raw('(SUM(starting_cash) + SUM(cash_in)) - SUM(expense_out) as total_saldo_kasir'),
                DB::raw('SUM(card_in) as total_card'),
                DB::raw('SUM(voucher_in) as total_voucher'),
                DB::raw('((SUM(starting_cash) + SUM(cash_in)) - SUM(expense_out)) + SUM(card_in) as total_grand')
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

        $unionQuery = $this->buildUnionQuery($dari_tanggal, $sampai_tanggal, $selectedBranchId, $kasir, $user);

        $reports = DB::query()->fromSub($unionQuery, 'aliran_kas')
            ->select(
                'tanggal',
                'nama_cabang',
                'nama_kasir',
                DB::raw('COUNT(CASE WHEN expense_out = 0 AND starting_cash = 0 THEN ref_id END) as jumlah_transaksi'),
                DB::raw('SUM(starting_cash) as total_modal_awal'),
                DB::raw('SUM(cash_in) as total_cash_masuk'),
                DB::raw('SUM(expense_out) as total_expense'),
                DB::raw('(SUM(starting_cash) + SUM(cash_in)) - SUM(expense_out) as saldo_kasir'),
                DB::raw('SUM(card_in) as total_card'),
                DB::raw('SUM(voucher_in) as total_voucher'),
                DB::raw('((SUM(starting_cash) + SUM(cash_in)) - SUM(expense_out)) + SUM(card_in) as total_grand')
            )
            ->groupBy('tanggal', 'branch_id', 'nama_cabang', 'cashier_id', 'nama_kasir')
            ->orderBy('tanggal', 'asc')
            ->orderBy('nama_kasir', 'asc')
            ->get();

        $filename = "Laporan_Audit_Kasir_{$dari_tanggal}_sd_{$sampai_tanggal}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($reports) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'No', 
                'Tanggal', 
                'Cabang', 
                'Nama Kasir', 
                'Jml Trx', 
                'Modal Awal (Rp)', 
                'Cash Masuk (Rp)', 
                'Pengeluaran Kas (Rp)', 
                'Saldo Kasir (Fisik) (Rp)', 
                'Card/QRIS/TF (Rp)', 
                'Voucher Diskon (Rp)', 
                'Total Setoran (Rp)'
            ]);

            $no = 1;
            $sumModal = 0;
            $sumCash = 0;
            $sumExpense = 0;
            $sumSaldo = 0;
            $sumCard = 0;
            $sumVoucher = 0;
            $sumGrand = 0;

            foreach ($reports as $row) {
                $sumModal += $row->total_modal_awal;
                $sumCash += $row->total_cash_masuk;
                $sumExpense += $row->total_expense;
                $sumSaldo += $row->saldo_kasir;
                $sumCard += $row->total_card;
                $sumVoucher += $row->total_voucher;
                $sumGrand += $row->total_grand;

                fputcsv($file, [
                    $no++,
                    $row->tanggal,
                    $row->nama_cabang ?? '-',
                    $row->nama_kasir,
                    $row->jumlah_transaksi,
                    $row->total_modal_awal,
                    $row->total_cash_masuk,
                    $row->total_expense,
                    $row->saldo_kasir,
                    $row->total_card,
                    $row->total_voucher,
                    $row->total_grand
                ]);
            }

            fputcsv($file, [
                '', 
                'TOTAL', 
                '', 
                '', 
                '', 
                $sumModal, 
                $sumCash, 
                $sumExpense, 
                $sumSaldo, 
                $sumCard, 
                $sumVoucher, 
                $sumGrand
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}