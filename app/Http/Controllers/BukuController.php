<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use App\Models\BukuModel;
use Illuminate\Http\Request;
use App\Traits\ResponseApiTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @group Buku
 * @groupDescription API Buku, Digunakan untuk memanggil fungsi yang berkaitan dengan modul Buku
 */
class BukuController extends Controller
{
    use ResponseApiTrait;

    public function __construct()
    {
        $this->bukuModel = new BukuModel();
    }

    public function index()
    {
        return view('pages.buku.main_buku');
    }

    public function formTambah()
    {
        return view('pages.buku.form_tambah_buku');
    }

    public function formUbah()
    {
        return view('pages.buku.form_ubah_buku');
    }

    /**
    * GET - Buku Cetak PDF List
    * @authenticated
    * @responseFile 200 response_docs_api/response_success_print.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListBukuPDF()
    {
        $data_buku = DB::table('buku as p')
            ->select('p.*', 'kategori_nama')
            ->join('buku_kategori', 'buku_kategori.kategori_id', '=', 'p.buku_kategori_id')
            ->whereNull('p.deleted_at')
            ->get();

        // Render HTML dari template Blade
        $html = view('pages.buku.form_cetak_pdf_buku', compact('data_buku'))->render();

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
        $filename = 'data-buku-' . date("Ymd-His") . '.pdf';
        $directory = public_path('print/pdf/buku');

        // Buat direktori jika belum ada
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Simpan file PDF di direktori public
        $filePath = $directory . '/' . $filename;
        file_put_contents($filePath, $output);

        // Buat URL untuk file PDF
        $url = asset('print/pdf/buku/' . $filename);

        return response()->json(['url' => $url]);
    }

    /**
    * GET - Buku Cetak Excel List
    * @authenticated
    * @responseFile 200 response_docs_api/response_success_print_xls.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListBukuExcel()
    {
        $data_buku = DB::table('buku as p')
            ->select('p.*', 'kategori_nama')
            ->join('buku_kategori', 'buku_kategori.kategori_id', '=', 'p.buku_kategori_id')
            ->whereNull('p.deleted_at')
            ->get();

        // Membuat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Menulis header
        $sheet->setCellValue('A1', 'Kode Buku');
        $sheet->setCellValue('B1', 'Nama Buku');
        $sheet->setCellValue('C1', 'Kategori Buku');
        // Menulis data
        $row = 2;
        foreach ($data_buku as $buku) {
            $sheet->setCellValue('A' . $row, $buku->buku_sku);
            $sheet->setCellValue('B' . $row, $buku->buku_nama);
            $sheet->setCellValue('C' . $row, $buku->kategori_nama);
            // Menambahkan kolom lain sesuai kebutuhan
            $row++;
        }

        // Mengatur header dan format file
        $filename = 'data-buku-' . date("Ymd-His") . '.xlsx';
        $path = 'print/excel/buku/' . $filename;

        // Membuat direktori jika belum ada
        $directory = public_path('print/excel/buku/');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Menyimpan file Excel ke dalam direktori public/uploads/excel/
        $writer = new Xlsx($spreadsheet);
        $writer->save($directory . '/' . $filename);

        // Menghasilkan URL untuk file yang baru saja disimpan
        $url = asset($path);

        return response()->json(['url' => $url]);
    }

    /**
    * POST - Buku List
    * @authenticated
    * @bodyParam start int required start data. Example: 0
    * @bodyParam limit int required limit data. Example: 10
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/buku/buku_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function bukuList(Request $request)
    {
        try {
            $request->validate([
                'start' => 'required',
                'limit' => 'required'
            ]);

            $result = $this->bukuModel->bukuList($request);
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
     * GET - Buku Detail
     * @authenticated
     * @urlParam buku_id int required buku_id data dari api/event list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function bukuDataDetail($buku_id)
    {
        try {
            $result = $this->bukuModel->bukuDataDetail($buku_id);
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
     * POST - Buku Create
     * @authenticated
     * @bodyParam buku_sku string buku_sku. Example: 00019230
     * @bodyParam buku_nama string buku_nama. Example: tes nama buku
     * @bodyParam buku_stok int buku_stok. Example: 1
     * @bodyParam buku_aktif string buku_aktif. Example: Aktif
     * @bodyParam buku_kategori_id int required buku_kategori_id data dari api/bukuKategori list. Example: 2
     * @bodyParam buku_denda int buku_denda. Example: 1000
     * @bodyParam buku_foto_path file required The buku_foto_path.
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function bukuCreate(Request $request)
    {

        try {
            $request->validate([
                'buku_sku' => 'required',
            ]);

            $buku_id = $this->bukuModel->bukuCreate($request);

            // Insert Detail
            if ($buku_id) {

                $msgSuccess = ["id" => $buku_id];
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
     * PUT - Buku Update
     * @authenticated
     * @bodyParam buku_id int buku_id. Example: 2
     * @bodyParam buku_sku string buku_sku. Example: 00019230
     * @bodyParam buku_nama string buku_nama. Example: tes nama buku
     * @bodyParam buku_stok int buku_stok. Example: 1
     * @bodyParam buku_aktif string buku_aktif. Example: Aktif
     * @bodyParam buku_kategori_id int required buku_kategori_id data dari api/bukuKategori list. Example: 2
     * @bodyParam buku_denda int buku_denda. Example: 1000
     * @bodyParam buku_foto_path file required The buku_foto_path.
     * @bodyParam buku_nama string buku_nama. Example: null
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function bukuUpdate(Request $request)
    {
        try {
            $request->validate([
                'buku_id' => 'required',
                'buku_sku' => 'required',
                'buku_nama' => 'required'
            ]);

            $buku_id    = $request->input('buku_id');

            // Fetch the record with the given $karyawan_id
            $cekMasterBuku = $this->bukuModel->find($request->input('buku_id'));
            // Cek data karyawan ada atau tidak
            if (!$cekMasterBuku) {
                return $this->showNotFound();
            }

            $result = $this->bukuModel->bukuUpdate($request);
            if ($result) {
                $msgSuccess = [
                    "id"          => $buku_id,
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

    /**
     * DELETE - Buku Delete
     * @authenticated
     * @urlParam buku_id int required buku_id data dari api/event list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function bukuDelete($buku_id)
    {
        try {
            $buku = BukuModel::findOrFail($buku_id);
            $buku->delete();

            if ($buku) {
                $msgSuccess = [
                    "id"          => $buku_id,
                    "dataUpdated" => $buku
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
