<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = today();

        // 1. Total Cash Inflow & Total Jumlah Transaksi Lunas Hari Ini
        $transaksiLunasQuery = Transaction::where('status', 'LUNAS')
            ->whereDate('created_at', $today);

        $totalCashInflow = (clone $transaksiLunasQuery)->sum('grand_total');
        $totalCountInflow = (clone $transaksiLunasQuery)->count();

        // 2 & 3. Omset + Jumlah Transaksi Unik per Tipe (Barang & Jasa)
        $omsetPerType = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transactions.status', 'LUNAS')
            ->whereDate('transactions.created_at', $today)
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

        // 4. Laba Kotor Hari Ini
        $labaKotor = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'LUNAS')
            ->whereDate('transactions.created_at', $today)
            ->sum(DB::raw('transaction_details.subtotal - (transaction_details.harga_beli * transaction_details.qty)'));

        // 5. Item Perlu Kulakan
        $emptyStockCount = Product::where('type', 'barang')
            ->where('is_active', 1)
            ->where('stock', '<=', 0)
            ->count();

        $lowStockCount = Product::where('type', 'barang')
            ->where('is_active', 1)
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        $totalPerluKulakan = $emptyStockCount + $lowStockCount;

        return view('dashboard.index', compact(
            'totalCashInflow',
            'totalCountInflow',
            'omsetBarang',
            'countTrxBarang',
            'omsetJasa',
            'countTrxJasa',
            'labaKotor',
            'totalPerluKulakan',
            'emptyStockCount',
            'lowStockCount'
        ));
    }
    public function index2()
    {
        $today = today();

        // 1. Total Cash Inflow (Arus Kas Masuk Hari Ini Dari Transaksi Lunas)
        $totalCashInflow = Transaction::where('status', 'LUNAS')
            ->whereDate('created_at', $today)
            ->sum('grand_total');

        // 2 & 3. Omset Barang & Jasa Hari Ini
        $omsetPerType = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_details.product_id', '=', 'products.id')
            ->where('transactions.status', 'LUNAS')
            ->whereDate('transactions.created_at', $today)
            ->select('products.type', DB::raw('SUM(transaction_details.subtotal) as total_omset'))
            ->groupBy('products.type')
            ->pluck('total_omset', 'type');

        $omsetBarang = $omsetPerType->get('barang', 0);
        $omsetJasa   = $omsetPerType->get('jasa', 0);

        // 4. Laba Kotor Hari Ini (Total Subtotal Detail - Total HPP Detail)
        $labaKotor = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.status', 'LUNAS')
            ->whereDate('transactions.created_at', $today)
            ->sum(DB::raw('transaction_details.subtotal - (transaction_details.harga_beli * transaction_details.qty)'));

        // 5. Item Perlu Kulakan (Barang aktif dengan stok <= min_stock)
        $emptyStockCount = Product::where('type', 'barang')
            ->where('is_active', 1)
            ->where('stock', '<=', 0)
            ->count();

        $lowStockCount = Product::where('type', 'barang')
            ->where('is_active', 1)
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        $totalPerluKulakan = $emptyStockCount + $lowStockCount;

        return view('dashboard.index', compact(
            'totalCashInflow',
            'omsetBarang',
            'omsetJasa',
            'labaKotor',
            'totalPerluKulakan',
            'emptyStockCount',
            'lowStockCount'
        ));
    }
}