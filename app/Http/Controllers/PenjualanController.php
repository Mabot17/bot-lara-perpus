<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Illuminate\Http\Request;
use App\Traits\ResponseApiTrait;
use Illuminate\Support\Facades\DB;
use App\Models\PenjualanModel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @group Penjualan
 * @groupDescription API Penjualan, Digunakan untuk memanggil fungsi yang berkaitan dengan modul Penjualan
 */
class PenjualanController extends Controller
{
    use ResponseApiTrait;

    public function __construct()
    {
        $this->penjualanModel = new PenjualanModel();
    }

    public function index()
    {
        return view('pages.penjualan.main_penjualan');
    }

    public function formTambah()
    {
        return view('pages.penjualan.form_tambah_penjualan');
    }

    public function formUbah()
    {
        return view('pages.penjualan.form_ubah_penjualan');
    }

    public function cetakListPenjualanPDF()
    {
        $data_penjualan = DB::table('penjualan as p')
            ->select('p.*')
            ->whereNull('p.deleted_at')
            ->get();

        $html = view('pages.penjualan.form_cetak_pdf_penjualan', compact('data_penjualan'))->render();

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
        $filename = 'data-produk-penjualan-' . date("Ymd-His") . '.pdf';
        $path = 'public/pdf/' . $filename;

        Storage::put($path, $output);

        $url = Storage::url($path);

        // $pdf->stream($filename);
        return response()->json(['url' => $url]);
    }

    public function cetakListPenjualanExcel()
    {
        $data_penjualan = DB::table('penjualan as p')
            ->select('p.*')
            ->whereNull('p.deleted_at')
            ->get();

        // Membuat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Menulis header
        $sheet->setCellValue('A1', 'No. Penjualan');
        $sheet->setCellValue('B1', 'Nama Pelanggan');
        $sheet->setCellValue('C1', 'Tanggal');
        $sheet->setCellValue('D1', 'Pembayaran');
        $sheet->setCellValue('E1', 'Total (Rp)');
        $sheet->setCellValue('F1', 'Total Bayar (Rp)');
        $sheet->setCellValue('G1', 'Total Kembalian (Rp)');
        // Menulis data
        $row = 2;
        foreach ($data_penjualan as $produk) {
            $sheet->setCellValue('A' . $row, $produk->penjualan_no);
            $sheet->setCellValue('B' . $row, $produk->penjualan_pelanggan);
            $sheet->setCellValue('C' . $row, $produk->penjualan_tanggal);
            $sheet->setCellValue('D' . $row, $produk->penjualan_cara_bayar);
            $sheet->setCellValue('E' . $row, $produk->penjualan_total);
            $sheet->setCellValue('F' . $row, $produk->penjualan_total_bayar);
            $sheet->setCellValue('G' . $row, $produk->penjualan_total_kembalian);
            // Menambahkan kolom lain sesuai kebutuhan
            $row++;
        }

        // Mengatur header dan format file
        $filename = 'data-produk-penjualan-' . date("Ymd-His") . '.xlsx';
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
    * Penjualan List
    * @authenticated
    * @bodyParam start int required start data. Example: 0
    * @bodyParam limit int required limit data. Example: 10
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/Penjualan/penjualan_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function penjualanList(Request $request)
    {
        try {
            $request->validate([
                'start' => 'required',
                'limit' => 'required'
            ]);

            $result = $this->penjualanModel->penjualanList($request);
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
     * Penjualan Detail
     * @authenticated
     * @urlParam penjualan_id int required penjualan_id data dari api/penjualan list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function penjualanDataDetail($penjualan_id)
    {
        try {
            $result = $this->penjualanModel->PenjualanDataDetail($penjualan_id);
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
     * Penjualan Create
     * @authenticated
     * @bodyParam penjualan_pelanggan string required Text biasa. Example: null
     * @bodyParam penjualan_tanggal date required penjualan_tanggal Example: 2024-06-14
     * @bodyParam penjualan_total int required Example: 10000
     * @bodyParam penjualan_total_bayar int penjualan_total_bayar Example: 10000
     * @bodyParam penjualan_cara_bayar enum required Contoh [Tunai, Kartu, Kredit, Transfer, skbdn] Example: Tunai
     * @bodyParam penjualan_total_kembalian int penjualan_total_bayar Example: 10000
     * @bodyParam penjualan_total_kembalian int penjualan_total_bayar Example: 10000
     * @bodyParam produk_list object[] Detail produk
     * @bodyParam produk_list[].pjual_detail_id int (Selalu null, flag create) Example: 0
     * @bodyParam produk_list[].pjual_detail_produk_id int required dari api/produk/list property produk_id Example: 1
     * @bodyParam produk_list[].pjual_detail_qty int required Example: 1
     * @bodyParam produk_list[].pjual_detail_harga int required Example: 1000
     * @bodyParam produk_list[].pjual_detail_diskon int required Example: 1
     * @bodyParam produk_list[].pjual_detail_diskon_rp int required Example: 1000
     * @bodyParam produk_list[].pjual_diskon_subtotal int required Example: 1000
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function penjualanCreate(Request $request)
    {

        try {
            $request->validate([
                'penjualan_pelanggan' => 'required'
            ]);

            $penjualan_id = $this->penjualanModel->PenjualanCreate($request);

            // Insert Detail
            if ($penjualan_id) {

                $msgSuccess = ["id" => $penjualan_id];
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
     * Penjualan Update
     * @authenticated
     * @bodyParam penjualan_ida int required penjualan_ida data dari api/penjualan list. Example: 2
     * @bodyParam penjualan_id int required Text biasa. Example: 1
     * @bodyParam penjualan_pelanggan string required Text biasa. Example: null
     * @bodyParam penjualan_tanggal date required penjualan_tanggal Example: 2024-06-14
     * @bodyParam penjualan_total int required Example: 10000
     * @bodyParam penjualan_total_bayar int penjualan_total_bayar Example: 10000
     * @bodyParam penjualan_cara_bayar enum required Contoh [Tunai, Kartu, Kredit, Transfer, skbdn] Example: Tunai
     * @bodyParam penjualan_total_kembalian int penjualan_total_bayar Example: 10000
     * @bodyParam penjualan_total_kembalian int penjualan_total_bayar Example: 10000
     * @bodyParam produk_list object[] Detail produk
     * @bodyParam produk_list[].pjual_detail_id int (Selalu null, flag create) Example: 0
     * @bodyParam produk_list[].pjual_detail_produk_id int required dari api/produk/list property produk_id Example: 1
     * @bodyParam produk_list[].pjual_detail_qty int required Example: 1
     * @bodyParam produk_list[].pjual_detail_harga int required Example: 1000
     * @bodyParam produk_list[].pjual_detail_diskon int required Example: 1
     * @bodyParam produk_list[].pjual_detail_diskon_rp int required Example: 1000
     * @bodyParam produk_list[].pjual_diskon_subtotal int required Example: 1000
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function penjualanUpdate(Request $request)
    {
        try {
            $penjualan_id    = $request->input('penjualan_id');

            // Fetch the record with the given $karyawan_id
            $cekMasterPenjualan = $this->penjualanModel->find($request->input('penjualan_id'));
            // Cek data karyawan ada atau tidak
            if (!$cekMasterPenjualan) {
                return $this->showNotFound();
            }

            $result = $this->penjualanModel->PenjualanUpdate($request);
            if ($result) {
                $msgSuccess = [
                    "id"          => $penjualan_id,
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
     * Penjualan Delete
     * @authenticated
     * @urlParam penjualan_id int required penjualan_id data dari api/penjualan list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function penjualanDelete($penjualan_id)
    {
        try {
            $mahasiswa = penjualanModel::findOrFail($penjualan_id);
            $mahasiswa->delete();

            if ($mahasiswa) {
                $msgSuccess = [
                    "id"          => $penjualan_id,
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
     * Penjualan Cetak Faktur
     * @authenticated
     * @urlParam penjualan_id int required penjualan_id data dari api/penjualan list. Example: 2
     * @responseFile 200 response_docs_api/response_success.json
     * @responseFile 404 response_docs_api/response_not_found.json
     */
    public function cetakFakturPDF($penjualan_id)
    {
        try {
            $result = $this->penjualanModel->PenjualanDataDetail($penjualan_id);
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
        $html = view('pages.penjualan.form_cetak_faktur', compact('result'))->render();

        $pdf = new Dompdf();
        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $output = $pdf->output();
        $filename = 'data-penjualan-' . date("Ymd-His") . '.pdf';
        $path = 'public/pdf/' . $filename;

        Storage::put($path, $output);

        $url = Storage::url($path);

        return response()->json(['url' => $url]);
        // $pdf->stream($filename);
    }
}
