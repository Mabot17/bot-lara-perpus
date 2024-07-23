<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Illuminate\Http\Request;
use App\Traits\ResponseApiTrait;
use Illuminate\Support\Facades\DB;
use App\Models\LaporanModel;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * @group Laporan
 * @groupDescription API Laporan - Digunakan mencari data laporan peminjaman dan pengembalian
 */
class LaporanController extends Controller
{
    use ResponseApiTrait;

    public function __construct()
    {
        $this->laporanModel = new LaporanModel();
    }

    public function laporanPeminjaman()
    {
        return view('laporan.peminjaman.main_laporan_peminjaman');
    }

    public function laporanPengembalian()
    {
        return view('laporan.pengembalian.main_laporan_pengembalian');
    }

    /**
    * POST - Peminjaman List
    * @authenticated
    * @bodyParam tgl_awal date required start data. Example:2024/07/01
    * @bodyParam tgl_akhir date required limit data. Example: 2024/07/01
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/Peminjaman/peminjaman_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function laporanPinjamanList(Request $request)
    {
        try {
            $request->validate([
                'tgl_awal' => 'required',
                'tgl_akhir' => 'required'
            ]);

            $result = $this->laporanModel->laporanPinjamanList($request);
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
    * POST - Laporan Peminjaman Cetak PDF
    * @authenticated
    * @bodyParam tgl_awal date required start data. Example:2024/07/01
    * @bodyParam tgl_akhir date required limit data. Example: 2024/07/01
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/Peminjaman/peminjaman_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListPeminjamanPDF(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $query = DB::table('peminjaman as p')
            ->select('p.*')
            ->whereNull('p.deleted_at');

        if ($tgl_awal && $tgl_akhir) {
            $query->whereBetween('p.peminjaman_tanggal', [$tgl_awal, $tgl_akhir]);
        }

        $data_peminjaman = $query->get();

        $html = view('laporan.peminjaman.laporan_peminjaman_pdf', compact('data_peminjaman'))->render();

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
    * POST - Laporan Peminjaman Cetak Xls
    * @authenticated
    * @bodyParam tgl_awal date required start data. Example:2024/07/01
    * @bodyParam tgl_akhir date required limit data. Example: 2024/07/01
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/Peminjaman/peminjaman_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListPeminjamanExcel(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $query = DB::table('peminjaman as p')
            ->select('p.*')
            ->whereNull('p.deleted_at');

        if ($tgl_awal && $tgl_akhir) {
            $query->whereBetween('p.peminjaman_tanggal', [$tgl_awal, $tgl_akhir]);
        }

        $data_peminjaman = $query->get();

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
    * POST - Pengembalian List
    * @authenticated
    * @bodyParam start int required start data. Example: 0
    * @bodyParam limit int required limit data. Example: 10
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/Pengembalian/pengembalian_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function laporanPengembalianList(Request $request)
    {
        try {
            $result = $this->laporanModel->pengembalianList($request);
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
    * POST - Laporan Pengembalian Cetak PDF
    * @authenticated
    * @bodyParam tgl_awal date required start data. Example:2024/07/01
    * @bodyParam tgl_akhir date required limit data. Example: 2024/07/01
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/Peminjaman/peminjaman_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListPengembalianPDF(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $query = DB::table('pengembalian as p')
            ->select('p.*', 'pj.peminjaman_pelanggan', 'pj.peminjaman_no')
            ->leftJoin('peminjaman as pj', 'pj.peminjaman_id', '=', 'p.pengembalian_pinjam_id')
            ->whereNull('p.deleted_at');

        if ($tgl_awal && $tgl_akhir) {
            $query->whereBetween('p.pengembalian_tanggal', [$tgl_awal, $tgl_akhir]);
        }

        $query->orderBy('pengembalian_id', 'asc');

        $data_pengembalian = $query->get();

        $html = view('laporan.pengembalian.laporan_pengembalian_pdf', compact('data_pengembalian'))->render();

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

    /**
    * POST - Laporan Pengembalian Cetak Xls
    * @authenticated
    * @bodyParam tgl_awal date required start data. Example:2024/07/01
    * @bodyParam tgl_akhir date required limit data. Example: 2024/07/01
    * @bodyParam filter string required Text biasa bisa diisi bisa tidak. Example: null
    * @responseFile 200 response_docs_api/Peminjaman/peminjaman_list.json
    * @responseFile 404 response_docs_api/response_not_found.json
    */
    public function cetakListPengembalianExcel(Request $request)
    {
        $tgl_awal = $request->tgl_awal;
        $tgl_akhir = $request->tgl_akhir;

        $query = DB::table('pengembalian as p')
            ->select('p.*', 'pj.peminjaman_pelanggan', 'pj.peminjaman_no')
            ->leftJoin('peminjaman as pj', 'pj.peminjaman_id', '=', 'p.pengembalian_pinjam_id')
            ->whereNull('p.deleted_at');

        if ($tgl_awal && $tgl_akhir) {
            $query->whereBetween('p.pengembalian_tanggal', [$tgl_awal, $tgl_akhir]);
        }

        $query->orderBy('pengembalian_id', 'asc');

        $data_pengembalian = $query->get();

        // Membuat spreadsheet baru
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Menulis header
        $sheet->setCellValue('A1', 'No. Pengembalian');
        $sheet->setCellValue('B1', 'Nama Pelanggan');
        $sheet->setCellValue('C1', 'Tanggal Est Kembali');
        $sheet->setCellValue('D1', 'Tanggal Kembali');
        $sheet->setCellValue('E1', 'Telat Hari');
        $sheet->setCellValue('F1', 'Total Denda (Rp)');

        // Menulis data
        $row = 2;
        foreach ($data_pengembalian as $buku) {
            $sheet->setCellValue('A' . $row, $buku->pengembalian_no);
            $sheet->setCellValue('B' . $row, $buku->peminjaman_pelanggan);
            $sheet->setCellValue('C' . $row, $buku->pengembalian_tanggal_est_kembali);
            $sheet->setCellValue('D' . $row, $buku->pengembalian_tanggal);
            $sheet->setCellValue('E' . $row, $buku->pengembalian_telat_hari);
            $sheet->setCellValue('F' . $row, $buku->pengembalian_total_denda);
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

}
