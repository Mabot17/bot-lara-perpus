<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProdukKategoriModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produk_kategori';
    protected $primaryKey = 'kategori_id';

    public function produkKategoriList($request) {

        $query = DB::table('produk_kategori as p')
            ->select('p.*')
            ->whereNull('p.deleted_at');

        $query->orderBy('kategori_id', 'desc');

        // Dipakai di response totalpaging
        $totalQuery = clone $query;
        $totalData = $totalQuery->count();

        $start = $request->input('start');
        $limit = $request->input('limit');
        if ($limit) {
            $query->offset($start)->limit($limit);
        }

        $dataProdukKategori = $query->get();

        if ($dataProdukKategori) {
            // Response Wajib dibuat seperti ini jika LIST
            $response = [
                'data'      => $dataProdukKategori,
                'totalData' => $totalData
            ];
            return $response;
        } else {
            return NULL;
        }
    }

    // ProdukKategori Detail Data contoh, satuan_konversi, nilai persediaan awal, dll
    public function produkKategoriDataDetail($kategori_id) {
        $query = DB::table('produk_kategori as p')
            ->select('p.*')
            ->where('p.kategori_id', $kategori_id);

        // JSON Diolah di controller
        $dataProdukKategori = $query->first(); // Retrieve the first record
        if ($dataProdukKategori) {
            return $dataProdukKategori;
        }else{
            return null;
        }

    }

    // Start ProdukKategori create
    public function produkKategoriCreate($request)
    {
        // Metode ORM Insert laravel (ambil field di $fillable)
        $this->kategori_kode = $request->input('kategori_kode' ?? null);
        $this->kategori_nama = $request->input('kategori_nama' ?? null);
        $this->created_by = Auth::user()->email;
        $this->created_at = date("Y-m-d H:i:s");

        $this->save();

        // Check if the insertion was successful
        if ($this->exists) { // Use $this->exists instead of $jualProdukKategori->exists
            // Return the ID of the inserted record
            return $this->kategori_id;
        } else {
            // Return an error indicator (e.g., -1)
            return -1;
        }
    }
    // End ProdukKategori create

     // Start ProdukKategori update
    public function produkKategoriUpdate($request)
    {
        $updMasterProdukKategori = $this->find($request->input('kategori_id'));

        // Metode ORM Insert laravel (ambil field di $fillable)
        $updMasterProdukKategori->kategori_kode = $request->input('kategori_kode' ?? null);
        $updMasterProdukKategori->kategori_nama = $request->input('kategori_nama' ?? null);
        $updMasterProdukKategori->updated_by = Auth::user()->email;
        $updMasterProdukKategori->updated_at = date("Y-m-d H:i:s");

        // Save the updated record
        $result = $updMasterProdukKategori->save();

        if ($result) {
            return $updMasterProdukKategori;
        } else {
            return NULL;
        }
    }
    // End ProdukKategori update
}
