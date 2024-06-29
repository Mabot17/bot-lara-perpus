<?php

namespace App\Models;

use Milon\Barcode\DNS1D;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProdukModel extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produk';
    protected $primaryKey = 'produk_id';

    public function produkList($request) {

        $query = DB::table('produk as p')
            ->select('p.*')
            ->whereNull('p.deleted_at');

        $query->orderBy('produk_id', 'desc');

        // Dipakai di response totalpaging
        $totalQuery = clone $query;
        $totalData = $totalQuery->count();

        $start = $request->input('start');
        $limit = $request->input('limit');
        if ($limit) {
            $query->offset($start)->limit($limit);
        }

        $dataProduk = $query->get();

        $barcode = new DNS1D();

        foreach ($dataProduk as $produk) {
            $produk->barcode = $barcode->getBarcodeHTML($produk->produk_sku, 'C128');
        }

        if ($dataProduk) {
            // Response Wajib dibuat seperti ini jika LIST
            $response = [
                'data'      => $dataProduk,
                'totalData' => $totalData
            ];
            return $response;
        } else {
            return NULL;
        }
    }

    // Produk Detail Data contoh, satuan_konversi, nilai persediaan awal, dll
    public function produkDataDetail($produk_id) {
        $query = DB::table('produk as p')
            ->select('p.*')
            ->where('p.produk_id', $produk_id);

        // JSON Diolah di controller
        $dataProduk = $query->first(); // Retrieve the first record
        if ($dataProduk) {
            return $dataProduk;
        }else{
            return null;
        }

    }

    // Start Produk create
    public function produkCreate($request)
    {
        // Metode ORM Insert laravel (ambil field di $fillable)
        $this->produk_sku = $request->input('produk_sku' ?? null);
        $this->produk_nama = $request->input('produk_nama' ?? null);
        $this->produk_satuan = $request->input('produk_satuan' ?? null);
        $this->produk_stok = $request->input('produk_stok' ?? null);
        $this->produk_aktif = $request->input('produk_aktif' ?? null);
        $this->produk_kategori_id = $request->input('produk_kategori_id' ?? null);
        $this->produk_harga = $request->input('produk_harga' ?? null);
        $this->created_by = Auth::user()->email;
        $this->created_at = date("Y-m-d H:i:s");

        // Simpan gambar ke direktori public gambar
        if ($request->hasFile('produk_foto_path')) {
            // Ambil nama file gambar
            $image = $request->file('produk_foto_path');
            $imageName = $image->getClientOriginalName();

            // Buat direktori jika belum ada
            $directory = public_path('uploads/produk/'.$request->produk_sku);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Pindahkan gambar ke direktori yang baru dibuat
            $image->move($directory, $imageName);
            $this->produk_foto_path = 'uploads/produk/'.$request->produk_sku.'/'.$imageName; // Simpan path gambar ke database
        }

        $this->save();

        // Check if the insertion was successful
        if ($this->exists) { // Use $this->exists instead of $jualProduk->exists
            // Return the ID of the inserted record
            return $this->produk_id;
        } else {
            // Return an error indicator (e.g., -1)
            return -1;
        }
    }
    // End Produk create

     // Start Produk update
    public function produkUpdate($request)
    {
        $updMasterProduk = $this->find($request->input('produk_id'));

        // Metode ORM Insert laravel (ambil field di $fillable)
        $updMasterProduk->produk_sku = $request->input('produk_sku' ?? null);
        $updMasterProduk->produk_nama = $request->input('produk_nama' ?? null);
        $updMasterProduk->produk_satuan = $request->input('produk_satuan' ?? null);
        $updMasterProduk->produk_stok = $request->input('produk_stok' ?? null);
        $updMasterProduk->produk_aktif = $request->input('produk_aktif' ?? null);
        $updMasterProduk->produk_kategori_id = $request->input('produk_kategori_id' ?? null);
        $updMasterProduk->produk_harga = $request->input('produk_harga' ?? null);
        $updMasterProduk->updated_by = Auth::user()->email;
        $updMasterProduk->updated_at = date("Y-m-d H:i:s");

        // Simpan gambar ke direktori public gambar
        if ($request->hasFile('produk_foto_path')) {
            // Ambil nama file gambar
            $image = $request->file('produk_foto_path');
            $imageName = $image->getClientOriginalName();

            // Buat direktori jika belum ada
            $directory = public_path('uploads/produk/'.$request->produk_sku);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Pindahkan gambar ke direktori yang baru dibuat
            $image->move($directory, $imageName);
            $updMasterProduk->produk_foto_path = 'uploads/produk/'.$request->produk_sku.'/'.$imageName; // Simpan path gambar ke database
        }

        // Save the updated record
        $result = $updMasterProduk->save();

        if ($result) {
            return $updMasterProduk;
        } else {
            return NULL;
        }
    }
    // End Produk update
}
