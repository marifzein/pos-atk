<?php

namespace App\Http\Controllers\Acc;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashExpense;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CashExpenseReportController extends Controller
{
    private function filterQuery(Request $request)
    {
        $query = CashExpense::with(['branch', 'user', 'shift'])
            ->latest('created_at');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->kategori);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $branches = Branch::where('is_active', 1)->get();

        // Hanya ambil user dengan role Kasir, Owner, atau Admin
        $cashiers = User::whereIn('role', ['Kasir', 'Owner', 'Admin'])
            ->when($request->filled('branch_id'), function ($q) use ($request) {
                return $q->where('branch_id', $request->branch_id);
            })
            ->orderBy('name')
            ->get();

        $query = $this->filterQuery($request);

        $totalNominal = (clone $query)->sum('nominal');
        $expenses = $query->paginate(25)->withQueryString();

        return view('laporan.pengeluaran_kas.index', compact('branches', 'cashiers', 'expenses', 'totalNominal'));
    }

    public function getCashiersByBranch($branchId)
    {
        // Hanya ambil user dengan role Kasir, Owner, atau Admin saat ganti cabang via Ajax
        $query = User::select('id', 'name')
            ->whereIn('role', ['Kasir', 'Owner', 'Admin']);

        if ($branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        $cashiers = $query->orderBy('name')->get();

        return response()->json($cashiers);
    }

    public function exportExcel(Request $request)
    {
        $expenses = $this->filterQuery($request)->get();
        $totalNominal = $expenses->sum('nominal');

        $fileName = 'Riwayat_Kas_Keluar_' . date('Ymd_His') . '.xls';

        return response(view('laporan.pengeluaran_kas.export_excel', compact('expenses', 'totalNominal', 'request')))
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    public function exportPdf(Request $request)
    {
        $expenses = $this->filterQuery($request)->get();
        $totalNominal = $expenses->sum('nominal');
        $branch = $request->filled('branch_id') ? Branch::find($request->branch_id) : null;

        return view('laporan.pengeluaran_kas.export_pdf', compact('expenses', 'totalNominal', 'request', 'branch'));
    }
}