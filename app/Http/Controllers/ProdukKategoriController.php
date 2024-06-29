<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Illuminate\Http\Request;
use App\Traits\ResponseApiTrait;
use Illuminate\Support\Facades\DB;
use App\Models\ProdukKategoriModel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @group Produk Kategori
 * @groupDescription API Produk Kategori, Digunakan untuk memanggil fungsi yang berkaitan dengan modul Produk Kategori
 */
class ProdukKategoriController extends Controller
{
    use ResponseApiTrait;

    public function __construct()
    {
        $this->ProdukKategoriModel = new ProdukKategoriModel();
    }

    public function index()
    {
        return view('pages.produk_kategori.main_produk_kategori');
    }

    public function formTambah()
    {
        return view('pages.produk_kategori.form_tambah_produk_kategori');
    }

    public function formUbah()
    {
        return view('pages.produk_kategori.form_ubah_produk_kategori');
    }

    public function formCetakKeteranganKuliah()
    {
        return view('pages.mahasiswa.form_isian_keterangan_kuliah');
    }

    /**
    * Produk Kategori Cetak PDF List
    * @authenticated
    * @responseFile 200 response_docs_api/response_success_print.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListProdukKategoriPDF()
    {
        $data_produk_kategori = DB::table('produk_kategori as p')
            ->select('p.*')
            ->whereNull('p.deleted_at')
            ->get();

        $html = view('pages.produk_kategori.form_cetak_pdf_produk_kategori', compact('data_produk_kategori'))->render();

        $pdf = new Dompdf();
        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        // Tambahkan nomor halaman
        $canvas = $pdf->getCanvas();
        $canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
            $text = "Page $pageNumber of $pageCount";
            $font = $fontMetrics->get_font('Arial, Helvetica, sans-serif', 'normal');
            $size = 12;
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $canvas->text(270, 820, $text, $font, $size);
        });

        $output = $pdf->output();
        $filename = 'data-produk-kategori-' . date("Ymd-His") . '.pdf';
        $path = 'public/pdf/' . $filename;

        Storage::put($path, $output);

        $url = Storage::url($path);

        // $pdf->stream($filename);
        return response()->json(['url' => $url]);
    }

    /**
    * Produk Kategori Cetak Excel List
    * @authenticated
    * @responseFile 200 response_docs_api/response_success_print.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListProdukKategoriExcel()
    {
        $data_produk_kategori = DB::table('produk_kategori as p')
            ->select('p.*')
            ->whereNull('p.deleted_at')
            ->get();

        // Membuat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Menulis header
        $sheet->setCellValue('A1', 'Kode Kategori');
        $sheet->setCellValue('B1', 'Nama Kategori');
        // Menulis data
        $row = 2;
        foreach ($data_produk_kategori as $produk) {
            $sheet->setCellValue('A' . $row, $produk->kategori_kode);
            $sheet->setCellValue('B' . $row, $produk->kategori_nama);
            // Menambahkan kolom lain sesuai kebutuhan
            $row++;
        }

        // Mengatur header dan format file
        $filename = 'data-produk-kategori-' . date("Ymd-His") . '.xlsx';
        $path = 'excel/' . $filename;

        // Membuat direktori jika belum ada
        $directory = 'public/excel';
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        // Menyimpan file Excel ke dalam direktori public/excel/
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('app/public/' . $path));

        // Menghasilkan URL untuk file yang baru saja disimpan
        $url = Storage::url($path);

        return response()->json(['url' => $url]);
    }

    /**
    * Produk Kategori List
    * @authenticated
    * @bodyParam start int required start data. Example: 0
    * @bodyParam limit int required limit data. Example: 10
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/ProdukKategori/produk_kategori_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function produkKategoriList(Request $request)
    {
        try {
            $request->validate([
                'start' => 'required',
                'limit' => 'required'
            ]);

            $result = $this->ProdukKategoriModel->produkKategoriList($request);
            if ($result['totalData']) {
                return $this->showSuccessList([
                    'data'        => $result['data'],
                    'totalData'   => $result['totalData'],
                    'codeMessage' => 'listTrue',
                    'isPaging'    => true
                ]);
            } else {
                return $this->showNotFound();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            $errors = $e->validator->errors()->all();
            return $this->showValidationResponse([
                'error' => $errors,
            ]);
        } catch (\Exception $e) {
            return $this->showBadResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Produk Kategori Detail
     * @authenticated
     * @urlParam kategori_id int required kategori_id data dari api/produk_kategori list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function ProdukKategoriDataDetail($kategori_id)
    {
        try {
            $result = $this->ProdukKategoriModel->ProdukKategoriDataDetail($kategori_id);
            if ($result) {
                return $this->showSuccess(['data' => $result]);
            } else {
                return $this->showNotFound();
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            $errors = $e->validator->errors()->all();
            return $this->showValidationResponse([
                'error' => $errors,
            ]);
        } catch (\Exception $e) {
            return $this->showBadResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Produk Kategori Create
     * @authenticated
     * @bodyParam ProdukKategori_kode string ProdukKategori_kode. Example: null
     * @bodyParam ProdukKategori_nama string ProdukKategori_nama. Example: null
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function ProdukKategoriCreate(Request $request)
    {

        try {
            $request->validate([
                'kategori_kode' => 'required',
                'kategori_nama' => 'required'
            ]);

            $kategori_id = $this->ProdukKategoriModel->ProdukKategoriCreate($request);

            // Insert Detail
            if ($kategori_id) {

                $msgSuccess = ["id" => $kategori_id];
                return $this->showSuccess([
                    'data'        => $msgSuccess,
                    'codeMessage' => 'createTrue'
                ]);
            } else {
                DB::rollBack();
                $result = 1;
                return $this->showSuccess([
                    'data'        => $result,
                    'codeMessage' => 'createFalse'
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            $errors = $e->validator->errors()->all();
            return $this->showValidationResponse([
                'error' => $errors,
            ]);
        } catch (\Exception $e) {
            return $this->showBadResponse(['error' => $e->getMessage()]);
        }
    }

    /**
     * Produk Kategori Update
     * @authenticated
     * @bodyParam kategori_id string required kategori_id. Example: 1
     * @bodyParam ProdukKategori_kode string ProdukKategori_kode. Example: null
     * @bodyParam ProdukKategori_nama string ProdukKategori_nama. Example: null
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function ProdukKategoriUpdate(Request $request)
    {
        try {
            $request->validate([
                'kategori_id' => 'required',
                'kategori_kode' => 'required',
                'kategori_nama' => 'required'
            ]);

            $kategori_id    = $request->input('kategori_id');

            // Fetch the record with the given $karyawan_id
            $cekMasterProdukKategori = $this->ProdukKategoriModel->find($request->input('kategori_id'));
            // Cek data karyawan ada atau tidak
            if (!$cekMasterProdukKategori) {
                return $this->showNotFound();
            }

            $result = $this->ProdukKategoriModel->ProdukKategoriUpdate($request);
            if ($result) {
                $msgSuccess = [
                    "id"          => $kategori_id,
                    "dataUpdated" => $result
                ];

                return $this->showSuccess([
                    'data'        => $msgSuccess,
                    'codeMessage' => 'updateTrue'
                ]);
            } else {
                return $this->showSuccess([
                    'data'        => null,
                    'codeMessage' => 'updateFalse'
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            $errors = $e->validator->errors()->all();
            return $this->showValidationResponse([
                'error' => $errors,
            ]);
        } catch (\Exception $e) {
            return $this->showBadResponse(['error' => $e->getMessage()]);
        }
    }

    public function produkKategoriDelete($kategori_id)
    {
        try {
            $mahasiswa = ProdukKategoriModel::findOrFail($kategori_id);
            $mahasiswa->delete();

            if ($mahasiswa) {
                $msgSuccess = [
                    "id"          => $kategori_id,
                    "dataUpdated" => $mahasiswa
                ];

                return $this->showSuccess([
                    'data'        => $msgSuccess,
                    'codeMessage' => 'updateTrue'
                ]);
            } else {
                return $this->showSuccess([
                    'data'        => null,
                    'codeMessage' => 'updateFalse'
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation errors
            $errors = $e->validator->errors()->all();
            return $this->showValidationResponse([
                'error' => $errors,
            ]);
        } catch (\Exception $e) {
            return $this->showBadResponse(['error' => $e->getMessage()]);
        }
    }
}
