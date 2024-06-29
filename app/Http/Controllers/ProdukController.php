<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use App\Models\ProdukModel;
use Illuminate\Http\Request;
use App\Traits\ResponseApiTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @group Produk
 * @groupDescription API Produk, Digunakan untuk memanggil fungsi yang berkaitan dengan modul Produk
 */
class ProdukController extends Controller
{
    use ResponseApiTrait;

    public function __construct()
    {
        $this->produkModel = new ProdukModel();
    }

    public function index()
    {
        return view('pages.produk.main_produk');
    }

    public function formTambah()
    {
        return view('pages.produk.form_tambah_produk');
    }

    public function formUbah()
    {
        return view('pages.produk.form_ubah_produk');
    }

    public function cetakListProdukPDF()
    {
        $data_produk = DB::table('produk as p')
            ->select('p.*', 'kategori_nama')
            ->join('produk_kategori', 'produk_kategori.kategori_id', '=', 'p.produk_kategori_id')
            ->whereNull('p.deleted_at')
            ->get();

        // Render HTML dari template Blade
        $html = view('pages.produk.form_cetak_pdf_produk', compact('data_produk'))->render();

        // Inisialisasi Dompdf
        $pdf = new Dompdf();
        $pdf->loadHtml($html);

        // Konfigurasi ukuran kertas dan orientasi
        $pdf->setPaper('A4', 'portrait');

        // Render PDF
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

        // Output PDF
        $output = $pdf->output();
        $filename = 'data-produk-' . date("Ymd-His") . '.pdf';
        $path = 'public/pdf/' . $filename;

        // Simpan PDF ke storage
        Storage::put($path, $output);

        // Kembalikan URL dari file PDF yang dihasilkan
        $url = Storage::url($path);

        return response()->json(['url' => $url]);
    }

    public function cetakListProdukExcel()
    {
        $data_produk = DB::table('produk as p')
            ->select('p.*', 'kategori_nama')
            ->join('produk_kategori', 'produk_kategori.kategori_id', '=', 'p.produk_kategori_id')
            ->whereNull('p.deleted_at')
            ->get();

        // Membuat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Menulis header
        $sheet->setCellValue('A1', 'Kode Produk');
        $sheet->setCellValue('B1', 'Nama Produk');
        $sheet->setCellValue('C1', 'Kategori Produk');
        // Menulis data
        $row = 2;
        foreach ($data_produk as $produk) {
            $sheet->setCellValue('A' . $row, $produk->produk_sku);
            $sheet->setCellValue('B' . $row, $produk->produk_nama);
            $sheet->setCellValue('C' . $row, $produk->kategori_nama);
            // Menambahkan kolom lain sesuai kebutuhan
            $row++;
        }

        // Mengatur header dan format file
        $filename = 'data-produk-' . date("Ymd-His") . '.xlsx';
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
    * Produk List
    * @authenticated
    * @bodyParam start int required start data. Example: 0
    * @bodyParam limit int required limit data. Example: 10
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/produk/produk_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function produkList(Request $request)
    {
        try {
            $request->validate([
                'start' => 'required',
                'limit' => 'required'
            ]);

            $result = $this->produkModel->produkList($request);
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
     * Produk Detail
     * @authenticated
     * @urlParam kategori_id int required kategori_id data dari api/event list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function produkDataDetail($kategori_id)
    {
        try {
            $result = $this->produkModel->produkDataDetail($kategori_id);
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
     * Produk Create
     * @authenticated
     * @bodyParam produk_sku string produk_sku. Example: null
     * @bodyParam produk_nama string produk_nama. Example: null
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function produkCreate(Request $request)
    {

        try {
            $request->validate([
                'produk_sku' => 'required',
                'produk_satuan' => 'required',
                'produk_satuan' => 'required'
            ]);

            $kategori_id = $this->produkModel->produkCreate($request);

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
     * Produk Update
     * @authenticated
     * @bodyParam kategori_id string required kategori_id. Example: 1
     * @bodyParam produk_sku string produk_sku. Example: null
     * @bodyParam produk_nama string produk_nama. Example: null
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function produkUpdate(Request $request)
    {
        try {
            // $request->validate([
            //     'produk_id' => 'required',
            //     'produk_sku' => 'required',
            //     'produk_nama' => 'required'
            // ]);

            $produk_id    = $request->input('produk_id');

            // Fetch the record with the given $karyawan_id
            $cekMasterProduk = $this->produkModel->find($request->input('produk_id'));
            // Cek data karyawan ada atau tidak
            if (!$cekMasterProduk) {
                return $this->showNotFound();
            }

            $result = $this->produkModel->produkUpdate($request);
            if ($result) {
                $msgSuccess = [
                    "id"          => $produk_id,
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

    public function produkDelete($produk_id)
    {
        try {
            $produk = ProdukModel::findOrFail($produk_id);
            $produk->delete();

            if ($produk) {
                $msgSuccess = [
                    "id"          => $produk_id,
                    "dataUpdated" => $produk
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
