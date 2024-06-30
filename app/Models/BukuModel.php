<?php

namespace App\Models;

use Milon\Barcode\DNS1D;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BukuModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'buku';
    protected $primaryKey = 'buku_id';

    public function bukuList($request) {

        $query = DB::table('buku as p')
            ->select('p.*')
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

        $barcode = new DNS1D();

        foreach ($dataBuku as $buku) {
            $buku->barcode = $barcode->getBarcodeHTML($buku->buku_sku, 'C128');
        }

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

    public function bukuDataDetail($buku_id) {
        $query = DB::table('buku as p')
            ->select('p.*')
            ->where('p.buku_id', $buku_id);

        // JSON Diolah di controller
        $dataBuku = $query->first(); // Retrieve the first record
        if ($dataBuku) {
            return $dataBuku;
        }else{
            return null;
        }

    }

    // Start Buku create
    public function bukuCreate($request)
    {
        // Metode ORM Insert laravel (ambil field di $fillable)
        $this->buku_sku = $request->input('buku_sku' ?? null);
        $this->buku_nama = $request->input('buku_nama' ?? null);
        $this->buku_satuan = $request->input('buku_satuan' ?? null);
        $this->buku_stok = $request->input('buku_stok' ?? null);
        $this->buku_aktif = $request->input('buku_aktif' ?? null);
        $this->buku_kategori_id = $request->input('buku_kategori_id' ?? null);
        $this->buku_denda = $request->input('buku_denda' ?? null);
        $this->created_by = Auth::user()->email;
        $this->created_at = date("Y-m-d H:i:s");

        // Simpan gambar ke direktori public gambar
        if ($request->hasFile('buku_foto_path')) {
            // Ambil nama file gambar
            $image = $request->file('buku_foto_path');
            $imageName = $image->getClientOriginalName();

            // Buat direktori jika belum ada
            $directory = public_path('uploads/buku/'.$request->buku_sku);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Pindahkan gambar ke direktori yang baru dibuat
            $image->move($directory, $imageName);
            $this->buku_foto_path = 'uploads/buku/'.$request->buku_sku.'/'.$imageName; // Simpan path gambar ke database
        }

        $this->save();

        // Check if the insertion was successful
        if ($this->exists) { // Use $this->exists instead of $jualBuku->exists
            // Return the ID of the inserted record
            return $this->buku_id;
        } else {
            // Return an error indicator (e.g., -1)
            return -1;
        }
    }
    // End Buku create

     // Start Buku update
    public function bukuUpdate($request)
    {
        $updMasterBuku = $this->find($request->input('buku_id'));

        // Metode ORM Insert laravel (ambil field di $fillable)
        $updMasterBuku->buku_sku = $request->input('buku_sku' ?? null);
        $updMasterBuku->buku_nama = $request->input('buku_nama' ?? null);
        $updMasterBuku->buku_stok = $request->input('buku_stok' ?? null);
        $updMasterBuku->buku_aktif = $request->input('buku_aktif' ?? null);
        $updMasterBuku->buku_kategori_id = $request->input('buku_kategori_id' ?? null);
        $updMasterBuku->buku_denda = $request->input('buku_denda' ?? null);
        $updMasterBuku->updated_by = Auth::user()->email;
        $updMasterBuku->updated_at = date("Y-m-d H:i:s");

        // Simpan gambar ke direktori public gambar
        if ($request->hasFile('buku_foto_path')) {
            // Ambil nama file gambar
            $image = $request->file('buku_foto_path');
            $imageName = $image->getClientOriginalName();

            // Buat direktori jika belum ada
            $directory = public_path('uploads/buku/'.$request->buku_sku);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Pindahkan gambar ke direktori yang baru dibuat
            $image->move($directory, $imageName);
            $updMasterBuku->buku_foto_path = 'uploads/buku/'.$request->buku_sku.'/'.$imageName; // Simpan path gambar ke database
        }

        // Save the updated record
        $result = $updMasterBuku->save();

        if ($result) {
            return $updMasterBuku;
        } else {
            return NULL;
        }
    }
    // End Buku update
}
