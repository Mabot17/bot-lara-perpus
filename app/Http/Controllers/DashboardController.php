<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('pages.dashboard.main_dashboard');
    }

    public function getDataGrafikBarNetsales()
    {
        $data = DB::table('penjualan')
                ->selectRaw('penjualan_tanggal, COUNT(*) as total_order, SUM(penjualan_detail.pjual_detail_qty) as total_item')
                ->join('penjualan_detail', 'penjualan.penjualan_id', '=', 'penjualan_detail.pjual_detail_master_id')
                ->groupBy('penjualan_tanggal')
                ->get();

        $dataSalesKategori = DB::table('penjualan_detail')
            ->selectRaw('kategori_nama, COUNT(*) as total_order, SUM(penjualan_detail.pjual_detail_qty) as total_item')
            ->join('produk', 'produk.produk_id', '=', 'penjualan_detail.pjual_detail_produk_id')
            ->join('produk_kategori', 'produk_kategori.kategori_id', '=', 'produk.produk_kategori_id')
            ->groupBy('produk.produk_kategori_id')
            ->get();

        $totalSales = DB::table('penjualan')
                ->sum('penjualan_total');

        $totalOrders = DB::table('penjualan')
                ->count();

        $conversionRate = 30; // Assuming you have a visitors table to calculate conversion

        $avgOrderValue = $totalSales / max(1, $totalOrders);

        return response()->json([
            'chart_data' => $data,
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'conversion_rate' => $conversionRate,
            'avg_order_value' => $avgOrderValue,
            'dataSalesKategori' => $dataSalesKategori,
        ]);
    }
}
