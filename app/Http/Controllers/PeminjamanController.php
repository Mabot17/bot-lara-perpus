<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Illuminate\Http\Request;
use App\Traits\ResponseApiTrait;
use Illuminate\Support\Facades\DB;
use App\Models\PeminjamanModel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @group Peminjaman
 * @groupDescription API Peminjaman, Digunakan untuk memanggil fungsi yang berkaitan dengan modul Peminjaman
 */
class PeminjamanController extends Controller
{
    use ResponseApiTrait;

    public function __construct()
    {
        $this->peminjamanModel = new PeminjamanModel();
    }

    public function index()
    {
        return view('pages.peminjaman.main_peminjaman');
    }

    public function formTambah()
    {
        return view('pages.peminjaman.form_tambah_peminjaman');
    }

    public function formUbah()
    {
        return view('pages.peminjaman.form_ubah_peminjaman');
    }

    /**
    * GET - Peminjaman Cetak PDF List
    * @authenticated
    * @responseFile 200 response_docs_api/response_success_print.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListPeminjamanPDF()
    {
        $data_peminjaman = DB::table('peminjaman as p')
            ->select('p.*')
            ->whereNull('p.deleted_at')
            ->get();

        $html = view('pages.peminjaman.form_cetak_pdf_peminjaman', compact('data_peminjaman'))->render();

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
        $filename = 'data-peminjaman-' . date("Ymd-His") . '.pdf';
        $directory = public_path('print/pdf/peminjaman');

        // Buat direktori jika belum ada
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Simpan file PDF di direktori public
        $filePath = $directory . '/' . $filename;
        file_put_contents($filePath, $output);

        // Buat URL untuk file PDF
        $url = asset('print/pdf/peminjaman/' . $filename);

        return response()->json(['url' => $url]);
    }

    /**
    * GET - Peminjaman Cetak Excel List
    * @authenticated
    * @responseFile 200 response_docs_api/response_success_print_xls.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListPeminjamanExcel()
    {
        $data_peminjaman = DB::table('peminjaman as p')
            ->select('p.*')
            ->whereNull('p.deleted_at')
            ->get();

        // Membuat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Menulis header
        $sheet->setCellValue('A1', 'No. Peminjaman');
        $sheet->setCellValue('B1', 'Nama Pelanggan');
        $sheet->setCellValue('C1', 'Tanggal Pinjam');
        $sheet->setCellValue('D1', 'Tanggal Est Kembali');
        $sheet->setCellValue('E1', 'Estimasi Total Denda (Rp)');

        // Menulis data
        $row = 2;
        foreach ($data_peminjaman as $buku) {
            $sheet->setCellValue('A' . $row, $buku->peminjaman_no);
            $sheet->setCellValue('B' . $row, $buku->peminjaman_pelanggan);
            $sheet->setCellValue('C' . $row, $buku->peminjaman_tanggal);
            $sheet->setCellValue('D' . $row, $buku->peminjaman_tanggal_est_kembali);
            $sheet->setCellValue('E' . $row, $buku->peminjaman_total_est_denda);
            // Menambahkan kolom lain sesuai kebutuhan
            $row++;
        }

        // Mengatur header dan format file
        $filename = 'data-peminjaman-' . date("Ymd-His") . '.xlsx';
        $path = 'print/excel/peminjaman/' . $filename;

        // Membuat direktori jika belum ada
        $directory = public_path('print/excel/peminjaman/');
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
    * POST - Peminjaman List
    * @authenticated
    * @bodyParam start int required start data. Example: 0
    * @bodyParam limit int required limit data. Example: 10
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/Peminjaman/peminjaman_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function peminjamanList(Request $request)
    {
        try {
            $request->validate([
                'start' => 'required',
                'limit' => 'required'
            ]);

            $result = $this->peminjamanModel->peminjamanList($request);
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
     * GET - Peminjaman Detail
     * @authenticated
     * @urlParam peminjaman_id int required peminjaman_id data dari api/peminjaman list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function peminjamanDataDetail($peminjaman_id)
    {
        try {
            $result = $this->peminjamanModel->PeminjamanDataDetail($peminjaman_id);
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
     * POST - Peminjaman Create
     * @authenticated
     * @bodyParam peminjaman_pelanggan string required Text biasa. Example: null
     * @bodyParam peminjaman_tanggal date required peminjaman_tanggal Example: 2024/07/01
     * @bodyParam peminjaman_tanggal_est_kembali date required peminjaman_tanggal_est_kembali Example: 2024/07/01
     * @bodyParam peminjaman_total_est_denda int required Example: 10000
     * @bodyParam peminjaman_stat_kembali int required Example: 1
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
    public function peminjamanCreate(Request $request)
    {

        try {
            $request->validate([
                'peminjaman_pelanggan' => 'required'
            ]);

            $peminjaman_id = $this->peminjamanModel->PeminjamanCreate($request);

            // Insert Detail
            if ($peminjaman_id) {

                $msgSuccess = ["id" => $peminjaman_id];
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
     * PUT - Peminjaman Update
     * @authenticated
     * @bodyParam peminjaman_id int required peminjaman_id data dari api/peminjaman list. Example: 2
     * @bodyParam peminjaman_no string required Text biasa. Example: PJ/2406-0002
     * @bodyParam peminjaman_pelanggan string required Text biasa. Example: Rohman
     * @bodyParam peminjaman_tanggal date required peminjaman_tanggal Example: 2024/07/01
     * @bodyParam peminjaman_tanggal_est_kembali date required peminjaman_tanggal_est_kembali Example: 2024/07/01
     * @bodyParam peminjaman_total_est_denda int required Example: 10000
     * @bodyParam peminjaman_stat_kembali int required Example: 1
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
    public function peminjamanUpdate(Request $request)
    {
        try {
            $peminjaman_id    = $request->input('peminjaman_id');

            // Fetch the record with the given $karyawan_id
            $cekMasterPeminjaman = $this->peminjamanModel->find($request->input('peminjaman_id'));
            // Cek data karyawan ada atau tidak
            if (!$cekMasterPeminjaman) {
                return $this->showNotFound();
            }

            $result = $this->peminjamanModel->PeminjamanUpdate($request);
            if ($result) {
                $msgSuccess = [
                    "id"          => $peminjaman_id,
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
     * DELETE - Peminjaman Delete
     * @authenticated
     * @urlParam peminjaman_id int required peminjaman_id data dari api/peminjaman list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function peminjamanDelete($peminjaman_id)
    {
        try {
            $mahasiswa = peminjamanModel::findOrFail($peminjaman_id);
            $mahasiswa->delete();

            if ($mahasiswa) {
                $msgSuccess = [
                    "id"          => $peminjaman_id,
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
     * GET - Peminjaman Cetak Faktur
     * @authenticated
     * @urlParam peminjaman_id int required peminjaman_id data dari api/peminjaman list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function cetakFakturPDF($peminjaman_id)
    {
        try {
            $result = $this->peminjamanModel->PeminjamanDataDetail($peminjaman_id);
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
        $html = view('pages.peminjaman.form_cetak_faktur', compact('result'))->render();

        $pdf = new Dompdf();
        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        // Output PDF
        $output = $pdf->output();
        $filename = 'data-peminjaman-' . date("Ymd-His") . '.pdf';
        $directory = public_path('print/pdf/peminjaman');

        // Buat direktori jika belum ada
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        // Simpan file PDF di direktori public
        $filePath = $directory . '/' . $filename;
        file_put_contents($filePath, $output);

        // Buat URL untuk file PDF
        $url = asset('print/pdf/peminjaman/' . $filename);

        return response()->json(['url' => $url]);

    }
}
