<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @group Dashboard
 * @groupDescription API Dashboard - Untuk melihat data masuk dan keluar di perpustakaan
 */
class DashboardController extends Controller
{
    public function index()
    {
        return view('pages.dashboard.main_dashboard');
    }

    /**
    * Dashboard Data List
    * @authenticated
    * @responseFile 200 response_docs_api/dashboard/dashboard_data_response.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function getDataGrafikBarNetsales()
    {
        $data = DB::table('peminjaman')
                ->selectRaw('peminjaman_tanggal, COUNT(*) as total_pinjam, SUM(peminjaman_detail.pinjam_detail_qty) as total_buku')
                ->join('peminjaman_detail', 'peminjaman.peminjaman_id', '=', 'peminjaman_detail.pinjam_detail_master_id')
                ->groupBy('peminjaman_tanggal')
                ->get();

        $dataPinjamByKategoriBuku = DB::table('peminjaman_detail')
            ->selectRaw('kategori_nama, COUNT(*) as total_order, SUM(peminjaman_detail.pinjam_detail_qty) as total_buku')
            ->join('buku', 'buku.buku_id', '=', 'peminjaman_detail.pinjam_detail_buku_id')
            ->join('buku_kategori', 'buku_kategori.kategori_id', '=', 'buku.buku_kategori_id')
            ->groupBy('buku.buku_kategori_id')
            ->get();

        $totalBuku = DB::table('buku')
                ->count();

        $totalBukuKategori = DB::table('buku_kategori')
                ->count();

        $totalPeminjaman = DB::table('peminjaman')
                ->where('peminjaman_stat_kembali', '=', 0)
                ->count();

        $totalPengembalian = DB::table('peminjaman')
                ->where('peminjaman_stat_kembali', '=', 1)
                ->count();

        return response()->json([
            'chart_bar_data' => $data,
            'chart_pie_data' => $dataPinjamByKategoriBuku,
            'total_buku' => $totalBuku,
            'total_buku_kategori' => $totalBukuKategori,
            'total_peminjaman' => $totalPeminjaman,
            'total_pengembalian' => $totalPengembalian,
        ]);
    }
}
