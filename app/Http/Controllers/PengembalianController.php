<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Illuminate\Http\Request;
use App\Traits\ResponseApiTrait;
use Illuminate\Support\Facades\DB;
use App\Models\PengembalianModel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @group Pengembalian
 * @groupDescription API Pengembalian, Digunakan untuk memanggil fungsi yang berkaitan dengan modul Pengembalian
 */
class PengembalianController extends Controller
{
    use ResponseApiTrait;

    public function __construct()
    {
        $this->pengembalianModel = new PengembalianModel();
    }

    public function index()
    {
        return view('pages.pengembalian.main_pengembalian');
    }

    public function formTambah()
    {
        return view('pages.pengembalian.form_tambah_pengembalian');
    }

    public function formUbah()
    {
        return view('pages.pengembalian.form_ubah_pengembalian');
    }

    public function cetakListPengembalianPDF()
    {
        $data_pengembalian = DB::table('pengembalian as p')
            ->select('p.*')
            ->whereNull('p.deleted_at')
            ->get();

        $html = view('pages.pengembalian.form_cetak_pdf_pengembalian', compact('data_pengembalian'))->render();

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


        // Output PDF
        $output = $pdf->output();
        $filename = 'data-pengembalian-' . date("Ymd-His") . '.pdf';
        $directory = public_path('print/pdf/pengembalian');

        // Buat direktori jika belum ada
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Simpan file PDF di direktori public
        $filePath = $directory . '/' . $filename;
        file_put_contents($filePath, $output);

        // Buat URL untuk file PDF
        $url = asset('print/pdf/pengembalian/' . $filename);

        return response()->json(['url' => $url]);
    }

    public function cetakListPengembalianExcel()
    {
        $data_pengembalian = DB::table('pengembalian as p')
            ->select('p.*')
            ->whereNull('p.deleted_at')
            ->get();

        // Membuat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Menulis header
        $sheet->setCellValue('A1', 'No. Pengembalian');
        $sheet->setCellValue('B1', 'Nama Pelanggan');
        $sheet->setCellValue('C1', 'Tanggal Pinjam');
        $sheet->setCellValue('D1', 'Tanggal Est Kembali');
        $sheet->setCellValue('E1', 'Estimasi Total Denda (Rp)');

        // Menulis data
        $row = 2;
        foreach ($data_pengembalian as $buku) {
            $sheet->setCellValue('A' . $row, $buku->pengembalian_no);
            $sheet->setCellValue('B' . $row, $buku->pengembalian_pelanggan);
            $sheet->setCellValue('C' . $row, $buku->pengembalian_tanggal);
            $sheet->setCellValue('D' . $row, $buku->pengembalian_tanggal_est_kembali);
            $sheet->setCellValue('E' . $row, $buku->pengembalian_total_est_denda);
            // Menambahkan kolom lain sesuai kebutuhan
            $row++;
        }

        // Mengatur header dan format file
        $filename = 'data-pengembalian-' . date("Ymd-His") . '.xlsx';
        $path = 'print/excel/pengembalian/' . $filename;

        // Membuat direktori jika belum ada
        $directory = public_path('print/excel/pengembalian/');
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
    * Pinjaman Buku List
    * @authenticated
    * @bodyParam start int required start data. Example: 0
    * @bodyParam limit int required limit data. Example: 10
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/Pengembalian/pengembalian_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function pinjamanList(Request $request)
    {
        try {
            $request->validate([
                'start' => 'required',
                'limit' => 'required'
            ]);

            $result = $this->pengembalianModel->pinjamanList($request);
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
    * Pengembalian Buku By Pinjaman ID List
    * @authenticated
    * @bodyParam start int required start data. Example: 0
    * @bodyParam limit int required limit data. Example: 10
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/buku/buku_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function bukuByPinjamanIdList(Request $request)
    {
        try {
            $request->validate([
                'start' => 'required',
                'limit' => 'required'
            ]);

            $result = $this->pengembalianModel->bukuByPinjamanIdList($request);
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
    * Pengembalian List
    * @authenticated
    * @bodyParam start int required start data. Example: 0
    * @bodyParam limit int required limit data. Example: 10
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/Pengembalian/pengembalian_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function pengembalianList(Request $request)
    {
        try {
            $request->validate([
                'start' => 'required',
                'limit' => 'required'
            ]);

            $result = $this->pengembalianModel->pengembalianList($request);
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
     * Pengembalian Detail
     * @authenticated
     * @urlParam pengembalian_id int required pengembalian_id data dari api/pengembalian list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function pengembalianDataDetail($pengembalian_id)
    {
        try {
            $result = $this->pengembalianModel->PengembalianDataDetail($pengembalian_id);
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
     * Pengembalian Create
     * @authenticated
     * @bodyParam pengembalian_pelanggan string required Text biasa. Example: null
     * @bodyParam pengembalian_tanggal date required pengembalian_tanggal Example: 2024-06-14
     * @bodyParam pengembalian_tanggal_est_kembali date required pengembalian_tanggal_est_kembali Example: 2024-06-14
     * @bodyParam pengembalian_total_est_denda int required Example: 10000
     * @bodyParam buku_list object[] Detail buku
     * @bodyParam buku_list[].pinjam_detail_id int (Selalu null, flag create) Example: 0
     * @bodyParam buku_list[].pinjam_detail_buku_id int required dari api/buku/list property buku_id Example: 1
     * @bodyParam buku_list[].pinjam_detail_qty int required Example: 1
     * @bodyParam buku_list[].pinjam_detail_harga int required Example: 1000
     * @bodyParam buku_list[].pinjam_detail_diskon int required Example: 1
     * @bodyParam buku_list[].pinjam_detail_diskon_rp int required Example: 1000
     * @bodyParam buku_list[].pinjam_diskon_subtotal int required Example: 1000
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function pengembalianCreate(Request $request)
    {

        try {
            $pengembalian_id = $this->pengembalianModel->PengembalianCreate($request);

            // Insert Detail
            if ($pengembalian_id) {

                $msgSuccess = ["id" => $pengembalian_id];
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
     * Pengembalian Update
     * @authenticated
     * @bodyParam pengembalian_ida int required pengembalian_ida data dari api/pengembalian list. Example: 2
     * @bodyParam pengembalian_id int required Text biasa. Example: 1
     * @bodyParam pengembalian_pelanggan string required Text biasa. Example: null
     * @bodyParam pengembalian_tanggal date required pengembalian_tanggal Example: 2024-06-14
     * @bodyParam pengembalian_tanggal_est_kembali date required pengembalian_tanggal_est_kembali Example: 2024-06-14
     * @bodyParam pengembalian_total_est_denda int required Example: 10000
     * @bodyParam buku_list object[] Detail buku
     * @bodyParam buku_list[].pinjam_detail_id int (Selalu null, flag create) Example: 0
     * @bodyParam buku_list[].pinjam_detail_buku_id int required dari api/buku/list property buku_id Example: 1
     * @bodyParam buku_list[].pinjam_detail_qty int required Example: 1
     * @bodyParam buku_list[].pinjam_detail_harga int required Example: 1000
     * @bodyParam buku_list[].pinjam_detail_diskon int required Example: 1
     * @bodyParam buku_list[].pinjam_detail_diskon_rp int required Example: 1000
     * @bodyParam buku_list[].pinjam_diskon_subtotal int required Example: 1000
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function pengembalianUpdate(Request $request)
    {
        try {
            $pengembalian_id    = $request->input('pengembalian_id');

            // Fetch the record with the given $karyawan_id
            $cekMasterPengembalian = $this->pengembalianModel->find($request->input('pengembalian_id'));
            // Cek data karyawan ada atau tidak
            if (!$cekMasterPengembalian) {
                return $this->showNotFound();
            }

            $result = $this->pengembalianModel->PengembalianUpdate($request);
            if ($result) {
                $msgSuccess = [
                    "id"          => $pengembalian_id,
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
     * Pengembalian Delete
     * @authenticated
     * @urlParam pengembalian_id int required pengembalian_id data dari api/pengembalian list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function pengembalianDelete($pengembalian_id)
    {
        try {
            $mahasiswa = pengembalianModel::findOrFail($pengembalian_id);
            $mahasiswa->delete();

            if ($mahasiswa) {
                $msgSuccess = [
                    "id"          => $pengembalian_id,
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

    /**
     * Pengembalian Cetak Faktur
     * @authenticated
     * @urlParam pengembalian_id int required pengembalian_id data dari api/pengembalian list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function cetakFakturPDF($pengembalian_id)
    {
        try {
            $result = $this->pengembalianModel->PengembalianDataDetail($pengembalian_id);
            if ($result) {
                $url = $this->generateFakturPdf($result);
                return $url;
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
    public function generateFakturPdf($result)
    {
        $html = view('pages.pengembalian.form_cetak_faktur', compact('result'))->render();

        $pdf = new Dompdf();
        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        // Output PDF
        $output = $pdf->output();
        $filename = 'data-pengembalian-' . date("Ymd-His") . '.pdf';
        $directory = public_path('print/pdf/pengembalian');

        // Buat direktori jika belum ada
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Simpan file PDF di direktori public
        $filePath = $directory . '/' . $filename;
        file_put_contents($filePath, $output);

        // Buat URL untuk file PDF
        $url = asset('print/pdf/pengembalian/' . $filename);

        return response()->json(['url' => $url]);

    }
}
