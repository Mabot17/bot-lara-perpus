<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BukuKategoriModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'buku_kategori';
    protected $primaryKey = 'kategori_id';

    public function BukuKategoriList($request) {

        $query = DB::table('buku_kategori as p')
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

        $dataBukuKategori = $query->get();

        if ($dataBukuKategori) {
            // Response Wajib dibuat seperti ini jika LIST
            $response = [
                'data'      => $dataBukuKategori,
                'totalData' => $totalData
            ];
            return $response;
        } else {
            return NULL;
        }
    }

    // BukuKategori Detail Data contoh, satuan_konversi, nilai persediaan awal, dll
    public function BukuKategoriDataDetail($kategori_id) {
        $query = DB::table('buku_kategori as p')
            ->select('p.*')
            ->where('p.kategori_id', $kategori_id);

        // JSON Diolah di controller
        $dataBukuKategori = $query->first(); // Retrieve the first record
        if ($dataBukuKategori) {
            return $dataBukuKategori;
        }else{
            return null;
        }

    }

    // Start BukuKategori create
    public function BukuKategoriCreate($request)
    {
        // Metode ORM Insert laravel (ambil field di $fillable)
        $this->kategori_kode = $request->input('kategori_kode' ?? null);
        $this->kategori_nama = $request->input('kategori_nama' ?? null);
        $this->created_by = Auth::user()->email;
        $this->created_at = date("Y-m-d H:i:s");

        $this->save();

        // Check if the insertion was successful
        if ($this->exists) { // Use $this->exists instead of $jualBukuKategori->exists
            // Return the ID of the inserted record
            return $this->kategori_id;
        } else {
            // Return an error indicator (e.g., -1)
            return -1;
        }
    }
    // End BukuKategori create

     // Start BukuKategori update
    public function BukuKategoriUpdate($request)
    {
        $updMasterBukuKategori = $this->find($request->input('kategori_id'));

        // Metode ORM Insert laravel (ambil field di $fillable)
        $updMasterBukuKategori->kategori_kode = $request->input('kategori_kode' ?? null);
        $updMasterBukuKategori->kategori_nama = $request->input('kategori_nama' ?? null);
        $updMasterBukuKategori->updated_by = Auth::user()->email;
        $updMasterBukuKategori->updated_at = date("Y-m-d H:i:s");

        // Save the updated record
        $result = $updMasterBukuKategori->save();

        if ($result) {
            return $updMasterBukuKategori;
        } else {
            return NULL;
        }
    }
    // End BukuKategori update
}
