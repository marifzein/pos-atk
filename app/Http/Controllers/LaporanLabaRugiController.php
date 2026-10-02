<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Branch;

class LaporanLabaRugiController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Default filter tanggal: dari awal bulan sampai hari ini
        $dari_tanggal = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->toDateString());
        $sampai_tanggal = $request->get('sampai_tanggal', Carbon::now()->toDateString());

        // Filter Cabang & Penguncian Otomatis Role Kasir
        $selectedBranchId = $request->get('branch_id');
        if ($user && strtolower($user->role) === 'kasir' && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
        }

        $branches = Branch::where('is_active', 1)->get();

        // Subquery Base: Join ke branches dan group per tanggal & cabang
        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->leftJoin('branches', 'transactions.branch_id', '=', 'branches.id')
            ->select(
                DB::raw('DATE(transactions.created_at) as tanggal'),
                'transactions.branch_id',
                'branches.name as nama_cabang',
                DB::raw('SUM(transaction_details.subtotal) as total_pendapatan'),
                DB::raw('SUM(transaction_details.qty * transaction_details.harga_beli) as total_hpp'),
                DB::raw('SUM(transaction_details.subtotal) - SUM(transaction_details.qty * transaction_details.harga_beli) as laba_kotor')
            )
            ->where('transactions.status', 'LUNAS')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dari_tanggal, $sampai_tanggal]);

        if (!empty($selectedBranchId)) {
            $query->where('transactions.branch_id', $selectedBranchId);
        }

        $reports = $query->groupBy(
                DB::raw('DATE(transactions.created_at)'),
                'transactions.branch_id',
                'branches.name'
            )
            ->orderBy('tanggal', 'asc')
            ->paginate(20)
            ->withQueryString();

        // Hitung total akumulasi di bagian bawah (Footer)
        $totalsQuery = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'LUNAS')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dari_tanggal, $sampai_tanggal]);

        if (!empty($selectedBranchId)) {
            $totalsQuery->where('transactions.branch_id', $selectedBranchId);
        }

        $totals = $totalsQuery->select(
            DB::raw('SUM(transaction_details.subtotal) as total_pendapatan'),
            DB::raw('SUM(transaction_details.qty * transaction_details.harga_beli) as total_hpp'),
            DB::raw('SUM(transaction_details.subtotal) - SUM(transaction_details.qty * transaction_details.harga_beli) as laba_kotor')
        )->first();

        return view('laporan.laba-rugi', compact('reports', 'totals', 'dari_tanggal', 'sampai_tanggal', 'branches', 'selectedBranchId'));
    }

    // Method untuk Export Excel
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $dari_tanggal = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->toDateString());
        $sampai_tanggal = $request->get('sampai_tanggal', Carbon::now()->toDateString());

        $selectedBranchId = $request->get('branch_id');
        if ($user && strtolower($user->role) === 'kasir' && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
        }

        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->leftJoin('branches', 'transactions.branch_id', '=', 'branches.id')
            ->select(
                DB::raw('DATE(transactions.created_at) as tanggal'),
                'transactions.branch_id',
                'branches.name as nama_cabang',
                DB::raw('SUM(transaction_details.subtotal) as total_pendapatan'),
                DB::raw('SUM(transaction_details.qty * transaction_details.harga_beli) as total_hpp'),
                DB::raw('SUM(transaction_details.subtotal) - SUM(transaction_details.qty * transaction_details.harga_beli) as laba_kotor')
            )
            ->where('transactions.status', 'LUNAS')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dari_tanggal, $sampai_tanggal]);

        if (!empty($selectedBranchId)) {
            $query->where('transactions.branch_id', $selectedBranchId);
        }

        $reports = $query->groupBy(
                DB::raw('DATE(transactions.created_at)'),
                'transactions.branch_id',
                'branches.name'
            )
            ->orderBy('tanggal', 'asc')
            ->get();

        $totalsQuery = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'LUNAS')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dari_tanggal, $sampai_tanggal]);

        if (!empty($selectedBranchId)) {
            $totalsQuery->where('transactions.branch_id', $selectedBranchId);
        }

        $totals = $totalsQuery->select(
            DB::raw('SUM(transaction_details.subtotal) as total_pendapatan'),
            DB::raw('SUM(transaction_details.qty * transaction_details.harga_beli) as total_hpp'),
            DB::raw('SUM(transaction_details.subtotal) - SUM(transaction_details.qty * transaction_details.harga_beli) as laba_kotor')
        )->first();

        $filename = "Laporan_Laba_Rugi_" . $dari_tanggal . "_s_d_" . $sampai_tanggal . ".xls";

        return response()
            ->view('laporan.laba-rugi-excel', compact('reports', 'totals', 'dari_tanggal', 'sampai_tanggal'))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    // Method untuk Cetak PDF
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $dari_tanggal = $request->get('dari_tanggal', Carbon::now()->startOfMonth()->toDateString());
        $sampai_tanggal = $request->get('sampai_tanggal', Carbon::now()->toDateString());

        $selectedBranchId = $request->get('branch_id');
        if ($user && strtolower($user->role) === 'kasir' && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
        }

        $query = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->leftJoin('branches', 'transactions.branch_id', '=', 'branches.id')
            ->select(
                DB::raw('DATE(transactions.created_at) as tanggal'),
                'transactions.branch_id',
                'branches.name as nama_cabang',
                DB::raw('SUM(transaction_details.subtotal) as total_pendapatan'),
                DB::raw('SUM(transaction_details.qty * transaction_details.harga_beli) as total_hpp'),
                DB::raw('SUM(transaction_details.subtotal) - SUM(transaction_details.qty * transaction_details.harga_beli) as laba_kotor')
            )
            ->where('transactions.status', 'LUNAS')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dari_tanggal, $sampai_tanggal]);

        if (!empty($selectedBranchId)) {
            $query->where('transactions.branch_id', $selectedBranchId);
        }

        $reports = $query->groupBy(
                DB::raw('DATE(transactions.created_at)'),
                'transactions.branch_id',
                'branches.name'
            )
            ->orderBy('tanggal', 'asc')
            ->get();

        $totalsQuery = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'LUNAS')
            ->whereBetween(DB::raw('DATE(transactions.created_at)'), [$dari_tanggal, $sampai_tanggal]);

        if (!empty($selectedBranchId)) {
            $totalsQuery->where('transactions.branch_id', $selectedBranchId);
        }

        $totals = $totalsQuery->select(
            DB::raw('SUM(transaction_details.subtotal) as total_pendapatan'),
            DB::raw('SUM(transaction_details.qty * transaction_details.harga_beli) as total_hpp'),
            DB::raw('SUM(transaction_details.subtotal) - SUM(transaction_details.qty * transaction_details.harga_beli) as laba_kotor')
        )->first();

        return view('laporan.laba-rugi-pdf', compact('reports', 'totals', 'dari_tanggal', 'sampai_tanggal'));
    }
}