<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengembalianModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengembalian';
    protected $primaryKey = 'pengembalian_id';

    public function pengembalianList($request) {

        $query = DB::table('pengembalian as p')
            ->select('p.*', 'pj.peminjaman_pelanggan')
            ->leftJoin('peminjaman as pj', 'pj.peminjaman_id', '=', 'p.pengembalian_pinjam_id')
            ->whereNull('p.deleted_at');

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

    public function pinjamanList($request) {

        $query = DB::table('peminjaman as p')
            ->select('p.*')
            ->where('p.peminjaman_stat_kembali', '=', 0)
            ->whereNull('p.deleted_at');

        $query->orderBy('peminjaman_id', 'asc');

        // Dipakai di response totalpaging
        $totalQuery = clone $query;
        $totalData = $totalQuery->count();

        $start = $request->input('start');
        $limit = $request->input('limit');
        if ($limit) {
            $query->offset($start)->limit($limit);
        }

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

    public function bukuByPinjamanIdList($request) {

        $query = DB::table('buku as p')
            ->select('p.*')
            ->leftJoin('peminjaman_detail as pd', 'p.buku_id', '=', 'pd.pinjam_detail_buku_id')
            ->where('pd.pinjam_detail_master_id', $request->input('peminjaman_id'))
            ->whereNull('p.deleted_at');

        $query->orderBy('buku_id', 'desc');

        // Dipakai di response totalpaging
        $totalQuery = clone $query;
        $totalData = $totalQuery->count();

        $start = $request->input('start');
        $limit = $request->input('limit');
        if ($limit) {
            $query->offset($start)->limit($limit);
        }

        $dataBuku = $query->get();

        if ($dataBuku) {
            // Response Wajib dibuat seperti ini jika LIST
            $response = [
                'data'      => $dataBuku,
                'totalData' => $totalData
            ];
            return $response;
        } else {
            return NULL;
        }
    }

    public function getDetailBukuList($pengembalian_id){
        $buku = DB::table('pengembalian_detail as p')
            ->select('p.*',
                DB::raw('pr.buku_nama as pkembali_detail_buku_nama')
            )
            ->leftJoin('buku AS pr', 'pr.buku_id', '=', 'p.pkembali_detail_buku_id')
            ->where('pkembali_detail_master_id', $pengembalian_id)
            ->get();

        return $buku;
    }

    // Pengembalian Detail Data contoh, pengembalian_detail, nilai persediaan awal, dll
    public function pengembalianDataDetail($pengembalian_id) {
        $query = DB::table('pengembalian as p')
            ->select('p.*', 'pj.peminjaman_pelanggan')
            ->leftJoin('peminjaman as pj', 'pj.peminjaman_id', '=', 'p.pengembalian_pinjam_id')
            ->where('p.pengembalian_id', $pengembalian_id);

        // JSON Diolah di controller
        $dataPengembalian = $query->first(); // Retrieve the first record
        if ($dataPengembalian) {
            $dataPengembalian->dataBukuList = $this->getDetailBukuList($dataPengembalian->pengembalian_id);
            return $dataPengembalian;
        }else{
            return null;
        }

    }

    public function generatePengembalianNo()
    {
        // Ambil tanggal saat ini
        $date = date('ym');
        // Ambil nomor pengembalian terakhir yang dibuat pada bulan dan tahun saat ini
        $lastPengembalian = $this->where('pengembalian_no', 'like', 'PK/'.$date.'-%')->orderBy('pengembalian_no', 'desc')->first();

        if ($lastPengembalian) {
            // Ambil nomor urut dari nomor pengembalian terakhir
            $lastNumber = intval(substr($lastPengembalian->pengembalian_no, -4));
        } else {
            $lastNumber = 0;
        }

        // Tambah 1 untuk nomor urut berikutnya
        $newNumber = $lastNumber + 1;
        // Format nomor pengembalian baru
        $newPengembalianNo = 'PK/' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return $newPengembalianNo;
    }

    // Start Pengembalian create
    public function pengembalianCreate($request)
    {
        // Metode ORM Insert laravel (ambil field di $fillable)
        $this->pengembalian_no = $this->generatePengembalianNo();
        $this->pengembalian_pinjam_id = $request->input('pengembalian_pinjam_id') ?? null;
        $this->pengembalian_tanggal_pinjam = $request->input('pengembalian_tanggal_pinjam') ?? null;
        $this->pengembalian_tanggal_est_kembali = $request->input('pengembalian_tanggal_est_kembali') ?? null;
        $this->pengembalian_tanggal = $request->input('pengembalian_tanggal') ?? null;
        $this->pengembalian_telat_hari = $request->input('pengembalian_telat_hari') ?? 0;
        $this->pengembalian_total_denda = $request->input('pengembalian_total_denda') ?? 0;
        $this->pengembalian_total_bayar = $request->input('pengembalian_total_bayar') ?? 0;
        $this->pengembalian_cara_bayar = $request->input('pengembalian_cara_bayar') ?? null;
        $this->pengembalian_total_kembalian = $request->input('pengembalian_total_kembalian') ?? null;
        $this->created_by = Auth::user()->email;
        $this->created_at = date("Y-m-d H:i:s");

        $this->save();

        // Check if the insertion was successful
        if ($this->exists) { // Use $this->exists instead of $jualPengembalian->exists
            // Return the ID of the inserted record
            $this->insertDetailBuku($this->pengembalian_id, $request->input('buku_list'));
            return $this->pengembalian_id;
        } else {
            // Return an error indicator (e.g., -1)
            return -1;
        }
    }

    // Start Pengembalian update
    public function pengembalianUpdate($request)
    {
        $updMasterPengembalian = $this->find($request->input('pengembalian_id'));

        // Metode ORM Insert laravel (ambil field di $fillable)
        $updMasterPengembalian->pengembalian_no = $request->input('pengembalian_no') ?? null;
        $updMasterPengembalian->pengembalian_pelanggan = $request->input('pengembalian_pelanggan') ?? null;
        $updMasterPengembalian->pengembalian_tanggal = $request->input('pengembalian_tanggal') ?? null;
        $updMasterPengembalian->pengembalian_tanggal_est_kembali = $request->input('pengembalian_tanggal_est_kembali') ?? null;
        $updMasterPengembalian->pengembalian_total_est_denda = $request->input('pengembalian_total_est_denda') ?? 0;

        $updMasterPengembalian->updated_by = Auth::user()->email;
        $updMasterPengembalian->updated_at = date("Y-m-d H:i:s");

        // Save the updated record
        $result = $updMasterPengembalian->save();

        if ($result) {
            $this->insertDetailBuku($updMasterPengembalian->pengembalian_id, $request->input('buku_list'));

            return $updMasterPengembalian;
        } else {
            return NULL;
        }
    }
    // End Pengembalian update

    public function insertDetailBuku($pengembalian_id, $dataBukuList = []){

        // Check if $dataBukuList is null
        if (is_null($dataBukuList)) {
            // Delete all records from the pengembalian_detail table where konversi_buku = $buku_id
            DB::table('pengembalian_detail')
                ->where('pkembali_detail_master_id', $pengembalian_id)
                ->delete();
            return; // Exit the function after deleting records
        }

        foreach ($dataBukuList as $detailBukuList) {
            $existingRecord = DB::table('pengembalian_detail')
                ->where('pkembali_detail_id', $detailBukuList['pinjam_detail_id'])
                ->first();

            if ($existingRecord) {
                // If the record with the given pinjam_detail_id exists, update the data
                DB::table('pengembalian_detail')
                    ->where('pkembali_detail_id', $detailBukuList['pinjam_detail_id'])
                    ->update([
                        'pkembali_detail_buku_id'    => $detailBukuList['pinjam_detail_buku_id'],
                        'pkembali_detail_qty'        => $detailBukuList['pinjam_detail_qty'],
                        'pkembali_detail_denda'      => $detailBukuList['pinjam_detail_denda'] ?? 0,
                        'pkembali_detail_telat_hari' => $detailBukuList['pinjam_detail_telat_hari'] ?? 0,
                        'pkembali_detail_diskon'     => $detailBukuList['pinjam_detail_diskon'] ?? 0,
                        'pkembali_detail_diskon_rp'  => $detailBukuList['pinjam_detail_diskon_rp'] ?? 0,
                        'pkembali_diskon_subtotal'   => $detailBukuList['pinjam_diskon_subtotal'] ?? 0,
                    ]);
            } else {
                DB::table('pengembalian_detail')->insert([
                    'pkembali_detail_buku_id' => $detailBukuList['pinjam_detail_buku_id'],
                    'pkembali_detail_master_id' => $pengembalian_id,
                    'pkembali_detail_qty'        => $detailBukuList['pinjam_detail_qty'],
                    'pkembali_detail_denda'      => $detailBukuList['pinjam_detail_denda'] ?? 0,
                    'pkembali_detail_telat_hari' => $detailBukuList['pinjam_detail_telat_hari'] ?? 0,
                    'pkembali_detail_diskon'     => $detailBukuList['pinjam_detail_diskon'] ?? 0,
                    'pkembali_detail_diskon_rp'  => $detailBukuList['pinjam_detail_diskon_rp'] ?? 0,
                    'pkembali_diskon_subtotal'   => $detailBukuList['pinjam_diskon_subtotal'] ?? 0,
                ]);
            }
        }
    }

    // End Pengembalian create
}
