<?php

namespace App\Http\Controllers\Laporan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockValuationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        // 💡 Ambil parameter sorting (Default urutkan berdasarkan Nilai Aset terbesar)
        $sortBy = $request->input('sort_by', 'total_nilai_aset'); 
        $sortDir = $request->input('sort_dir', 'desc');

        // Validasi kolom sorting agar aman dari SQL Injection
        $allowedSorts = ['sku', 'name', 'stock', 'hpp_average', 'harga_jual', 'total_nilai_aset', 'total_potensi_omset'];
        if (!in_array($sortBy, $allowedSorts)) $sortBy = 'total_nilai_aset';
        if (!in_array($sortDir, ['asc', 'desc'])) $sortDir = 'desc';

        // Query Base
        $query = DB::table('products')
            ->select(
                'sku',
                'name',
                'stock',
                'purchase_price as hpp_average',
                'price as harga_jual',
                DB::raw('(stock * purchase_price) as total_nilai_aset'),
                DB::raw('(stock * price) as total_potensi_omset')
            )
            ->where('stock', '>', 0);

        // Tambah filter pencarian jika diisi
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Aplikasikan sorting dinamis
        $query->orderBy($sortBy, $sortDir);

        // Menghitung total seluruh isi toko (sesuai filter pencarian)
        $totalAsetToko = DB::table('products')
            ->select(
                DB::raw('SUM(stock * purchase_price) as grand_total_aset'),
                DB::raw('SUM(stock * price) as grand_total_jual')
            )
            ->where('stock', '>', 0)
            ->when($search, function($q) use ($search) {
                $q->where(function($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->first();

        // Cek Aksi Export
        $exportType = $request->input('export');

        if ($exportType === 'excel') {
            // $reportData = $query->input();
            $reportData = $query->get();
            $filename = "Laporan_Nilai_Aset_Stok_" . now()->format('Y-m-d') . ".xls";
            
            return response()->view('laporan.nilai-aset-stok.excel', compact('reportData', 'totalAsetToko'))
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', "attachment; filename={$filename}")
                ->header('Cache-Control', 'max-age=0');
        }

        if ($exportType === 'pdf') {
            // $reportData = $query->input();
            $reportData = $query->get();
            return view('laporan.nilai-aset-stok.pdf', compact('reportData', 'totalAsetToko'));
        }

        // Tampilan Standar Web
        $reportData = $query->paginate(30)->withQueryString();

        return view('laporan.nilai-aset-stok.index', compact('reportData', 'totalAsetToko', 'search', 'sortBy', 'sortDir'));
    }
}