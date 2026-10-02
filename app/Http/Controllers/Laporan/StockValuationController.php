<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;

class StockValuationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        
        // Filter Cabang
        $selectedBranchId = $request->input('branch_id');
        if ($user && strtolower($user->role) === 'kasir' && $user->branch_id) {
            $selectedBranchId = $user->branch_id;
        }

        $branches = Branch::where('is_active', 1)->get();

        // Parameter sorting
        $sortBy = $request->input('sort_by', 'total_nilai_aset'); 
        $sortDir = $request->input('sort_dir', 'desc');

        $allowedSorts = ['nama_cabang', 'sku', 'name', 'stock', 'hpp_average', 'harga_jual', 'total_nilai_aset', 'total_potensi_omset'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'total_nilai_aset';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        // Base Query: Join product_stocks ke products dan branches
        // Mengabaikan products.stock, hanya type = 'barang' dan stock cabang > 0
        $query = DB::table('product_stocks')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->leftJoin('branches', 'product_stocks.branch_id', '=', 'branches.id')
            ->select(
                'product_stocks.branch_id',
                'branches.name as nama_cabang',
                'products.sku',
                'products.barcode',
                'products.name',
                'product_stocks.stock as stock',
                'products.purchase_price as hpp_average',
                'products.price as harga_jual',
                DB::raw('(product_stocks.stock * products.purchase_price) as total_nilai_aset'),
                DB::raw('(product_stocks.stock * products.price) as total_potensi_omset')
            )
            ->where('products.type', 'barang')
            ->where('product_stocks.stock', '>', 0);

        // Filter Cabang
        if (!empty($selectedBranchId)) {
            $query->where('product_stocks.branch_id', $selectedBranchId);
        }

        // Filter Pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%")
                  ->orWhere('products.barcode', 'like', "%{$search}%");
            });
        }

        // Grand Total Header & Footer
        $totalsQuery = DB::table('product_stocks')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->where('products.type', 'barang')
            ->where('product_stocks.stock', '>', 0);

        if (!empty($selectedBranchId)) {
            $totalsQuery->where('product_stocks.branch_id', $selectedBranchId);
        }

        if ($search) {
            $totalsQuery->where(function($q) use ($search) {
                $q->where('products.name', 'like', "%{$search}%")
                  ->orWhere('products.sku', 'like', "%{$search}%")
                  ->orWhere('products.barcode', 'like', "%{$search}%");
            });
        }

        $totalAsetToko = $totalsQuery->select(
            DB::raw('SUM(product_stocks.stock * products.purchase_price) as grand_total_aset'),
            DB::raw('SUM(product_stocks.stock * products.price) as grand_total_jual'),
            DB::raw('SUM(product_stocks.stock) as grand_total_qty')
        )->first();

        // Export Excel
        $exportType = $request->input('export');
        if ($exportType === 'excel') {
            $reportData = $query->orderBy($sortBy, $sortDir)->get();
            $filename = "Laporan_Nilai_Aset_Stok_" . now()->format('Y-m-d') . ".xls";
            
            return response()->view('laporan.nilai-aset-stok.excel', compact('reportData', 'totalAsetToko'))
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', "attachment; filename={$filename}")
                ->header('Cache-Control', 'max-age=0');
        }

        // Export PDF
        if ($exportType === 'pdf') {
            $reportData = $query->orderBy($sortBy, $sortDir)->get();
            return view('laporan.nilai-aset-stok.pdf', compact('reportData', 'totalAsetToko'));
        }

        // Tampilan Web
        $reportData = $query->orderBy($sortBy, $sortDir)->paginate(30)->withQueryString();

        return view('laporan.nilai-aset-stok.index', compact(
            'reportData', 'totalAsetToko', 'search', 'sortBy', 'sortDir', 'branches', 'selectedBranchId'
        ));
    }
}