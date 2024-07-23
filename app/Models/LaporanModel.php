<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaporanModel extends Model
{
    use HasFactory, SoftDeletes;

    public function laporanPinjamanList($request) {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $query = DB::table('peminjaman as p')
            ->select('p.*')
            ->whereNull('p.deleted_at');

        if ($tgl_awal && $tgl_akhir) {
            $query->whereBetween('p.peminjaman_tanggal', [$tgl_awal, $tgl_akhir]);
        }

        $query->orderBy('p.peminjaman_id', 'asc');

        // Dipakai di response totalpaging
        $totalQuery = clone $query;
        $totalData = $totalQuery->count();
        $dataPeminjaman = $query->get();

        if ($dataPeminjaman) {
            // Response Wajib dibuat seperti ini jika LIST
            $response = [
                'data'      => $dataPeminjaman,
                'totalData' => $totalData
            ];
            return $response;
        } else {
            return NULL;
        }
    }

    public function pengembalianList($request) {

        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $query = DB::table('pengembalian as p')
            ->select('p.*', 'pj.peminjaman_pelanggan', 'pj.peminjaman_no')
            ->leftJoin('peminjaman as pj', 'pj.peminjaman_id', '=', 'p.pengembalian_pinjam_id')
            ->whereNull('p.deleted_at');

        if ($tgl_awal && $tgl_akhir) {
            $query->whereBetween('p.pengembalian_tanggal', [$tgl_awal, $tgl_akhir]);
        }

        $query->orderBy('pengembalian_id', 'asc');

        // Dipakai di response totalpaging
        $totalQuery = clone $query;
        $totalData = $totalQuery->count();

        $start = $request->input('start');
        $limit = $request->input('limit');
        if ($limit) {
            $query->offset($start)->limit($limit);
        }

        $dataPengembalian = $query->get();

        if ($dataPengembalian) {
            // Response Wajib dibuat seperti ini jika LIST
            $response = [
                'data'      => $dataPengembalian,
                'totalData' => $totalData
            ];
            return $response;
        } else {
            return NULL;
        }
    }
}
