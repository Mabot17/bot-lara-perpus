<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Illuminate\Http\Request;
use App\Traits\ResponseApiTrait;
use Illuminate\Support\Facades\DB;
use App\Models\BukuKategoriModel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @group Buku Kategori
 * @groupDescription API Buku Kategori, Digunakan untuk memanggil fungsi yang berkaitan dengan modul Buku Kategori
 */
class BukuKategoriController extends Controller
{
    use ResponseApiTrait;

    public function __construct()
    {
        $this->BukuKategoriModel = new BukuKategoriModel();
    }

    public function index()
    {
        return view('pages.buku_kategori.main_buku_kategori');
    }

    public function formTambah()
    {
        return view('pages.buku_kategori.form_tambah_buku_kategori');
    }

    public function formUbah()
    {
        return view('pages.buku_kategori.form_ubah_buku_kategori');
    }

    public function formCetakKeteranganKuliah()
    {
        return view('pages.mahasiswa.form_isian_keterangan_kuliah');
    }

    /**
    * Buku Kategori Cetak PDF List
    * @authenticated
    * @responseFile 200 response_docs_api/response_success_print.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListBukuKategoriPDF()
    {
        $data_buku_kategori = DB::table('buku_kategori as p')
            ->select('p.*')
            ->whereNull('p.deleted_at')
            ->get();

        $html = view('pages.buku_kategori.form_cetak_pdf_buku_kategori', compact('data_buku_kategori'))->render();

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
        $filename = 'data-kategori-buku-' . date("Ymd-His") . '.pdf';
        $directory = public_path('print/pdf/kategori-buku');

        // Buat direktori jika belum ada
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Simpan file PDF di direktori public
        $filePath = $directory . '/' . $filename;
        file_put_contents($filePath, $output);

        // Buat URL untuk file PDF
        $url = asset('print/pdf/kategori-buku/' . $filename);

        return response()->json(['url' => $url]);
    }

    /**
    * Buku Kategori Cetak Excel List
    * @authenticated
    * @responseFile 200 response_docs_api/response_success_print_xls.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListBukuKategoriExcel()
    {
        $data_buku_kategori = DB::table('buku_kategori as p')
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
        foreach ($data_buku_kategori as $buku) {
            $sheet->setCellValue('A' . $row, $buku->kategori_kode);
            $sheet->setCellValue('B' . $row, $buku->kategori_nama);
            // Menambahkan kolom lain sesuai kebutuhan
            $row++;
        }

        // Mengatur header dan format file
        $filename = 'data-kategori-buku-' . date("Ymd-His") . '.xlsx';
        $path = 'print/excel/kategori-buku/' . $filename;

        // Membuat direktori jika belum ada
        $directory = public_path('print/excel/kategori-buku/');
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
    * POST - Buku Kategori List
    * @authenticated
    * @bodyParam start int required start data. Example: 0
    * @bodyParam limit int required limit data. Example: 10
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/BukuKategori/buku_kategori_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function BukuKategoriList(Request $request)
    {
        try {
            $request->validate([
                'start' => 'required',
                'limit' => 'required'
            ]);

            $result = $this->BukuKategoriModel->BukuKategoriList($request);
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
     * GET - Buku Kategori Detail
     * @authenticated
     * @urlParam kategori_id int required kategori_id data dari api/buku_kategori list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function BukuKategoriDataDetail($kategori_id)
    {
        try {
            $result = $this->BukuKategoriModel->BukuKategoriDataDetail($kategori_id);
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
     * POST - Buku Kategori Create
     * @authenticated
     * @bodyParam kategori_kode string kategori_kode. Example: null
     * @bodyParam kategori_nama string kategori_nama. Example: null
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function BukuKategoriCreate(Request $request)
    {

        try {
            $request->validate([
                'kategori_kode' => 'required',
                'kategori_nama' => 'required'
            ]);

            $kategori_id = $this->BukuKategoriModel->BukuKategoriCreate($request);

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
     * PUT - Buku Kategori Update
     * @authenticated
     * @bodyParam kategori_id string required kategori_id. Example: 1
     * @bodyParam kategori_kode string kategori_kode. Example: null
     * @bodyParam kategori_nama string kategori_nama. Example: null
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function BukuKategoriUpdate(Request $request)
    {
        try {
            $request->validate([
                'kategori_id' => 'required',
                'kategori_kode' => 'required',
                'kategori_nama' => 'required'
            ]);

            $kategori_id    = $request->input('kategori_id');

            // Fetch the record with the given $karyawan_id
            $cekMasterBukuKategori = $this->BukuKategoriModel->find($request->input('kategori_id'));
            // Cek data karyawan ada atau tidak
            if (!$cekMasterBukuKategori) {
                return $this->showNotFound();
            }

            $result = $this->BukuKategoriModel->BukuKategoriUpdate($request);
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

    /**
     * DELETE - Buku Kategori Delete
     * @authenticated
     * @urlParam kategori_id int required kategori_id data dari api/kategori list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function BukuKategoriDelete($kategori_id)
    {
        try {
            $mahasiswa = BukuKategoriModel::findOrFail($kategori_id);
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
