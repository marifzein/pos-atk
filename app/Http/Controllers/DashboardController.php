<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        // JIKA STAFF JASA -> LEMPAR LANGSUNG KE PESANAN JASA
        if ($user->role === 'Staff Jasa') {
            return redirect()->route('pesanan-jasa.index');
        }

        // JIKA STAFF BARANG -> LEMPAR LANGSUNG KE PESANAN BARANG
        if ($user->role === 'Staff Barang') {
            return redirect()->route('pesanan-barang.index');
        }

        // 1. Tentukan Range Tanggal Berdasarkan Request Filter
        $period = $request->get('period', 'today'); // default: today
        $startDate = null;
        $endDate = null;

        if ($period === 'today') {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        } elseif ($period === 'this_week') {
            $startDate = Carbon::now()->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();
        } elseif ($period === 'this_month') {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
        } elseif ($period === 'custom') {
            $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::today()->startOfDay();
            $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::today()->endOfDay();
        } else {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        }

        // 2. Total Cash Inflow & Total Jumlah Transaksi Lunas
        $transaksiLunasQuery = Transaction::where('status', 'LUNAS')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalCashInflow = (clone $transaksiLunasQuery)->sum('grand_total');
        $totalCountInflow = (clone $transaksiLunasQuery)->count();

        // 3. Omset & Jumlah Transaksi Unik per Tipe (Barang & Jasa)
        $omsetPerType = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transactions.status', 'LUNAS')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->select(
                'products.type', 
                DB::raw('SUM(transaction_details.subtotal) as total_omset'),
                DB::raw('COUNT(DISTINCT transactions.id) as total_trx')
            )
            ->groupBy('products.type')
            ->get()
            ->keyBy('type');

        $omsetBarang = $omsetPerType->get('barang')->total_omset ?? 0;
        $countTrxBarang = $omsetPerType->get('barang')->total_trx ?? 0;

        $omsetJasa = $omsetPerType->get('jasa')->total_omset ?? 0;
        $countTrxJasa = $omsetPerType->get('jasa')->total_trx ?? 0;

        // 4. Laba Kotor
        $labaKotor = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'LUNAS')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->sum(DB::raw('transaction_details.subtotal - (transaction_details.harga_beli * transaction_details.qty)'));

        // 5. Item Perlu Kulakan

        // A. Hitung total kejadian HABIS TOTAL per cabang (product_stocks.stock <= 0)
        $emptyStockCount = DB::table('product_stocks')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->where('products.type', 'barang')
            ->where('products.is_active', 1)
            ->where('product_stocks.stock', '<=', 0)
            ->count();

        // B. Hitung total kejadian STOK MENIPIS per cabang (stock > 0 DAN stock <= min_stock)
        $lowStockCount = DB::table('product_stocks')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->where('products.type', 'barang')
            ->where('products.is_active', 1)
            ->where('product_stocks.stock', '>', 0)
            ->whereColumn('product_stocks.stock', '<=', 'product_stocks.min_stock')
            ->count();

        // C. Hitung JUMLAH PRODUK UNIK yang kena dampak (minimal ada 1 cabang habis ATAU menipis)
        $totalPerluKulakan = DB::table('product_stocks')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->where('products.type', 'barang')
            ->where('products.is_active', 1)
            ->where(function ($query) {
                $query->where('product_stocks.stock', '<=', 0)
                    ->orWhereColumn('product_stocks.stock', '<=', 'product_stocks.min_stock');
            })
            ->distinct('product_stocks.product_id')
            ->count('product_stocks.product_id');

        
        // return view('dashboard.index', compact(
        //     'period',
        //     'startDate',
        //     'endDate',
        //     'totalCashInflow',
        //     'totalCountInflow',
        //     'omsetBarang',
        //     'countTrxBarang',
        //     'omsetJasa',
        //     'countTrxJasa',
        //     'labaKotor',
        //     'totalPerluKulakan',
        //     'emptyStockCount',
        //     'lowStockCount'
        // ));

        // --- DATA CHART ---

        // CHART 1: Tren Penjualan
        $dateFormat = $period === 'today' ? '%H:00' : '%Y-%m-%d';
        $salesTrend = Transaction::where('status', 'LUNAS')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$dateFormat}') as label"),
                DB::raw('SUM(grand_total) as total')
            )
            ->groupBy('label')
            ->orderBy('label', 'ASC')
            ->pluck('total', 'label');

        // CHART 2: Top 5 Produk Terlaris
        $topProducts = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'LUNAS')
            ->whereBetween('transactions.created_at', [$startDate, $endDate])
            ->select('transaction_details.nama_barang', DB::raw('SUM(transaction_details.qty) as total_qty'))
            ->groupBy('transaction_details.nama_barang')
            ->orderBy('total_qty', 'DESC')
            ->limit(5)
            ->get();

        // CHART 3: Metode Pembayaran
        $paymentMethods = Transaction::where('status', 'LUNAS')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(cash) as Tunai'),
                DB::raw('SUM(card) as Non_Tunai'),
                DB::raw('SUM(voucher) as Voucher')
            )
            ->first();

        $paymentData = [
            'Tunai' => (float) ($paymentMethods->Tunai ?? 0),
            'Non-Tunai / Card' => (float) ($paymentMethods->Non_Tunai ?? 0),
            'Voucher' => (float) ($paymentMethods->Voucher ?? 0),
        ];

        return view('dashboard.index', compact(
            'period', 'startDate', 'endDate',
            'totalCashInflow', 'totalCountInflow',
            'omsetBarang', 'countTrxBarang',
            'omsetJasa', 'countTrxJasa',
            'labaKotor', 'totalPerluKulakan',
            'emptyStockCount', 'lowStockCount',
            'salesTrend', 'topProducts', 'paymentData'
        ));
    }
}