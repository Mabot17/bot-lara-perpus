<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PenjualanModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'penjualan';
    protected $primaryKey = 'penjualan_id';

    public function penjualanList($request) {

        $query = DB::table('penjualan as p')
            ->select('p.*')
            ->whereNull('p.deleted_at');

        $query->orderBy('penjualan_id', 'asc');

        // Dipakai di response totalpaging
        $totalQuery = clone $query;
        $totalData = $totalQuery->count();

        $start = $request->input('start');
        $limit = $request->input('limit');
        if ($limit) {
            $query->offset($start)->limit($limit);
        }

        $dataPenjualan = $query->get();

        if ($dataPenjualan) {
            // Response Wajib dibuat seperti ini jika LIST
            $response = [
                'data'      => $dataPenjualan,
                'totalData' => $totalData
            ];
            return $response;
        } else {
            return NULL;
        }
    }

    public function getDetailProdukList($penjualan_id){
        $produk = DB::table('penjualan_detail as p')
            ->select('p.*',
                DB::raw('pr.produk_nama as pjual_detail_produk_nama')
            )
            ->leftJoin('produk AS pr', 'pr.produk_id', '=', 'p.pjual_detail_produk_id')
            ->where('pjual_detail_master_id', $penjualan_id)
            ->get();

        return $produk;
    }

    // Penjualan Detail Data contoh, penjualan_detail, nilai persediaan awal, dll
    public function penjualanDataDetail($penjualan_id) {
        $query = DB::table('penjualan as p')
            ->select('p.*')
            ->where('p.penjualan_id', $penjualan_id);

        // JSON Diolah di controller
        $dataPenjualan = $query->first(); // Retrieve the first record
        if ($dataPenjualan) {
            $dataPenjualan->dataProdukList = $this->getDetailProdukList($dataPenjualan->penjualan_id);
            return $dataPenjualan;
        }else{
            return null;
        }

    }

    public function generatePenjualanNo()
    {
        // Ambil tanggal saat ini
        $date = date('ym');
        // Ambil nomor penjualan terakhir yang dibuat pada bulan dan tahun saat ini
        $lastPenjualan = $this->where('penjualan_no', 'like', 'PJ/'.$date.'-%')->orderBy('penjualan_no', 'desc')->first();

        if ($lastPenjualan) {
            // Ambil nomor urut dari nomor penjualan terakhir
            $lastNumber = intval(substr($lastPenjualan->penjualan_no, -4));
        } else {
            $lastNumber = 0;
        }

        // Tambah 1 untuk nomor urut berikutnya
        $newNumber = $lastNumber + 1;
        // Format nomor penjualan baru
        $newPenjualanNo = 'PJ/' . $date . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return $newPenjualanNo;
    }

    // Start Penjualan create
    public function penjualanCreate($request)
    {
        // Metode ORM Insert laravel (ambil field di $fillable)
        $this->penjualan_no = $this->generatePenjualanNo();
        $this->penjualan_pelanggan = $request->input('penjualan_pelanggan') ?? null;
        $this->penjualan_tanggal = $request->input('penjualan_tanggal') ?? null;
        $this->penjualan_total = $request->input('penjualan_total') ?? 0;
        $this->penjualan_total_bayar = $request->input('penjualan_total_bayar') ?? 0;
        $this->penjualan_cara_bayar = $request->input('penjualan_cara_bayar') ?? null;
        $this->penjualan_total_kembalian = $request->input('penjualan_total_kembalian') ?? 0;
        $this->created_by = Auth::user()->email;
        $this->created_at = date("Y-m-d H:i:s");

        $this->save();

        // Check if the insertion was successful
        if ($this->exists) { // Use $this->exists instead of $jualPenjualan->exists
            // Return the ID of the inserted record
            $this->insertDetailProduk($this->penjualan_id, $request->input('produk_list'));
            return $this->penjualan_id;
        } else {
            // Return an error indicator (e.g., -1)
            return -1;
        }
    }

    // Start Penjualan update
    public function penjualanUpdate($request)
    {
        $updMasterPenjualan = $this->find($request->input('penjualan_id'));

        // Metode ORM Insert laravel (ambil field di $fillable)
        $updMasterPenjualan->penjualan_no = $request->input('penjualan_no') ?? null;
        $updMasterPenjualan->penjualan_pelanggan = $request->input('penjualan_pelanggan') ?? null;
        $updMasterPenjualan->penjualan_tanggal = $request->input('penjualan_tanggal') ?? null;
        $updMasterPenjualan->penjualan_total = $request->input('penjualan_total') ?? 0;
        $updMasterPenjualan->penjualan_total_bayar = $request->input('penjualan_total_bayar') ?? 0;
        $updMasterPenjualan->penjualan_cara_bayar = $request->input('penjualan_cara_bayar') ?? null;
        $updMasterPenjualan->penjualan_total_kembalian = $request->input('penjualan_total_kembalian') ?? 0;

        $updMasterPenjualan->updated_by = Auth::user()->email;
        $updMasterPenjualan->updated_at = date("Y-m-d H:i:s");

        // Save the updated record
        $result = $updMasterPenjualan->save();

        if ($result) {
            $this->insertDetailProduk($updMasterPenjualan->penjualan_id, $request->input('produk_list'));

            return $updMasterPenjualan;
        } else {
            return NULL;
        }
    }
    // End Penjualan update

    public function insertDetailProduk($penjualan_id, $dataProdukList = []){
        // Check if $dataProdukList is null
        if (is_null($dataProdukList)) {
            // Delete all records from the penjualan_detail table where konversi_produk = $produk_id
            DB::table('penjualan_detail')
                ->where('pjual_detail_master_id', $penjualan_id)
                ->delete();
            return; // Exit the function after deleting records
        }

        foreach ($dataProdukList as $detailProdukList) {
            $existingRecord = DB::table('penjualan_detail')
                ->where('pjual_detail_id', $detailProdukList['pjual_detail_id'])
                ->first();

            if ($existingRecord) {
                // If the record with the given pjual_detail_id exists, update the data
                DB::table('penjualan_detail')
                    ->where('pjual_detail_id', $detailProdukList['pjual_detail_id'])
                    ->update([
                        'pjual_detail_produk_id' => $detailProdukList['pjual_detail_produk_id'],
                        'pjual_detail_qty'       => $detailProdukList['pjual_detail_qty'],
                        'pjual_detail_harga'     => $detailProdukList['pjual_detail_harga'] ?? 0,
                        'pjual_detail_diskon'    => $detailProdukList['pjual_detail_diskon'] ?? 0,
                        'pjual_detail_diskon_rp' => $detailProdukList['pjual_detail_diskon_rp'] ?? 0,
                        'pjual_diskon_subtotal'  => $detailProdukList['pjual_diskon_subtotal'] ?? 0,
                    ]);
            } else {
                DB::table('penjualan_detail')->insert([
                    'pjual_detail_produk_id' => $detailProdukList['pjual_detail_produk_id'],
                    'pjual_detail_master_id' => $penjualan_id,
                    'pjual_detail_qty'       => $detailProdukList['pjual_detail_qty'],
                    'pjual_detail_harga'     => $detailProdukList['pjual_detail_harga'] ?? 0,
                    'pjual_detail_diskon'    => $detailProdukList['pjual_detail_diskon'] ?? 0,
                    'pjual_detail_diskon_rp' => $detailProdukList['pjual_detail_diskon_rp'] ?? 0,
                    'pjual_diskon_subtotal'  => $detailProdukList['pjual_diskon_subtotal'] ?? 0,
                ]);
            }
        }
    }

    // End Penjualan create
}
