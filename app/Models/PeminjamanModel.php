<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PeminjamanModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'peminjaman';
    protected $primaryKey = 'peminjaman_id';

    public function peminjamanList($request) {

        $query = DB::table('peminjaman as p')
            ->select('p.*')
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

    public function getDetailBukuList($peminjaman_id){
        $buku = DB::table('peminjaman_detail as p')
            ->select('p.*',
                DB::raw('pr.buku_nama as pinjam_detail_buku_nama')
            )
            ->leftJoin('buku AS pr', 'pr.buku_id', '=', 'p.pinjam_detail_buku_id')
            ->where('pinjam_detail_master_id', $peminjaman_id)
            ->get();

        return $buku;
    }

    // Peminjaman Detail Data contoh, peminjaman_detail, nilai persediaan awal, dll
    public function peminjamanDataDetail($peminjaman_id) {
        $query = DB::table('peminjaman as p')
            ->select('p.*')
            ->where('p.peminjaman_id', $peminjaman_id);

        // JSON Diolah di controller
        $dataPeminjaman = $query->first(); // Retrieve the first record
        if ($dataPeminjaman) {
            $dataPeminjaman->dataBukuList = $this->getDetailBukuList($dataPeminjaman->peminjaman_id);
            return $dataPeminjaman;
        }else{
            return null;
        }

    }

    public function generatePeminjamanNo()
    {
        // Ambil tanggal saat ini
        $date = date('ym');
        // Ambil nomor peminjaman terakhir yang dibuat pada bulan dan tahun saat ini
        $lastPeminjaman = $this->where('peminjaman_no', 'like', 'PJ/'.$date.'-%')->orderBy('peminjaman_no', 'desc')->first();

        if ($lastPeminjaman) {
            // Ambil nomor urut dari nomor peminjaman terakhir
            $lastNumber = intval(substr($lastPeminjaman->peminjaman_no, -4));
        } else {
            $lastNumber = 0;
        }

        // Tambah 1 untuk nomor urut berikutnya
        $newNumber = $lastNumber + 1;
        // Format nomor peminjaman baru
        $newPeminjamanNo = 'PJ/' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return $newPeminjamanNo;
    }

    // Start Peminjaman create
    public function peminjamanCreate($request)
    {
        // Metode ORM Insert laravel (ambil field di $fillable)
        $this->peminjaman_no = $this->generatePeminjamanNo();
        $this->peminjaman_pelanggan = $request->input('peminjaman_pelanggan') ?? null;
        $this->peminjaman_tanggal = $request->input('peminjaman_tanggal') ?? null;
        $this->peminjaman_total = $request->input('peminjaman_total') ?? 0;
        $this->peminjaman_total_bayar = $request->input('peminjaman_total_bayar') ?? 0;
        $this->peminjaman_cara_bayar = $request->input('peminjaman_cara_bayar') ?? null;
        $this->peminjaman_total_kembalian = $request->input('peminjaman_total_kembalian') ?? 0;
        $this->created_by = Auth::user()->email;
        $this->created_at = date("Y-m-d H:i:s");

        $this->save();

        // Check if the insertion was successful
        if ($this->exists) { // Use $this->exists instead of $jualPeminjaman->exists
            // Return the ID of the inserted record
            $this->insertDetailBuku($this->peminjaman_id, $request->input('buku_list'));
            return $this->peminjaman_id;
        } else {
            // Return an error indicator (e.g., -1)
            return -1;
        }
    }

    // Start Peminjaman update
    public function peminjamanUpdate($request)
    {
        $updMasterPeminjaman = $this->find($request->input('peminjaman_id'));

        // Metode ORM Insert laravel (ambil field di $fillable)
        $updMasterPeminjaman->peminjaman_no = $request->input('peminjaman_no') ?? null;
        $updMasterPeminjaman->peminjaman_pelanggan = $request->input('peminjaman_pelanggan') ?? null;
        $updMasterPeminjaman->peminjaman_tanggal = $request->input('peminjaman_tanggal') ?? null;
        $updMasterPeminjaman->peminjaman_total = $request->input('peminjaman_total') ?? 0;
        $updMasterPeminjaman->peminjaman_total_bayar = $request->input('peminjaman_total_bayar') ?? 0;
        $updMasterPeminjaman->peminjaman_cara_bayar = $request->input('peminjaman_cara_bayar') ?? null;
        $updMasterPeminjaman->peminjaman_total_kembalian = $request->input('peminjaman_total_kembalian') ?? 0;

        $updMasterPeminjaman->updated_by = Auth::user()->email;
        $updMasterPeminjaman->updated_at = date("Y-m-d H:i:s");

        // Save the updated record
        $result = $updMasterPeminjaman->save();

        if ($result) {
            $this->insertDetailBuku($updMasterPeminjaman->peminjaman_id, $request->input('buku_list'));

            return $updMasterPeminjaman;
        } else {
            return NULL;
        }
    }
    // End Peminjaman update

    public function insertDetailBuku($peminjaman_id, $dataBukuList = []){
        // Check if $dataBukuList is null
        if (is_null($dataBukuList)) {
            // Delete all records from the peminjaman_detail table where konversi_buku = $buku_id
            DB::table('peminjaman_detail')
                ->where('pinjam_detail_master_id', $peminjaman_id)
                ->delete();
            return; // Exit the function after deleting records
        }

        foreach ($dataBukuList as $detailBukuList) {
            $existingRecord = DB::table('peminjaman_detail')
                ->where('pinjam_detail_id', $detailBukuList['pinjam_detail_id'])
                ->first();

            if ($existingRecord) {
                // If the record with the given pinjam_detail_id exists, update the data
                DB::table('peminjaman_detail')
                    ->where('pinjam_detail_id', $detailBukuList['pinjam_detail_id'])
                    ->update([
                        'pinjam_detail_buku_id' => $detailBukuList['pinjam_detail_buku_id'],
                        'pinjam_detail_qty'       => $detailBukuList['pinjam_detail_qty'],
                        'pinjam_detail_harga'     => $detailBukuList['pinjam_detail_harga'] ?? 0,
                        'pinjam_detail_diskon'    => $detailBukuList['pinjam_detail_diskon'] ?? 0,
                        'pinjam_detail_diskon_rp' => $detailBukuList['pinjam_detail_diskon_rp'] ?? 0,
                        'pinjam_diskon_subtotal'  => $detailBukuList['pinjam_diskon_subtotal'] ?? 0,
                    ]);
            } else {
                DB::table('peminjaman_detail')->insert([
                    'pinjam_detail_buku_id' => $detailBukuList['pinjam_detail_buku_id'],
                    'pinjam_detail_master_id' => $peminjaman_id,
                    'pinjam_detail_qty'       => $detailBukuList['pinjam_detail_qty'],
                    'pinjam_detail_harga'     => $detailBukuList['pinjam_detail_harga'] ?? 0,
                    'pinjam_detail_diskon'    => $detailBukuList['pinjam_detail_diskon'] ?? 0,
                    'pinjam_detail_diskon_rp' => $detailBukuList['pinjam_detail_diskon_rp'] ?? 0,
                    'pinjam_diskon_subtotal'  => $detailBukuList['pinjam_diskon_subtotal'] ?? 0,
                ]);
            }
        }
    }

    // End Peminjaman create
}
