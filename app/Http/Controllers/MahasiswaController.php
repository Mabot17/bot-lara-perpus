<?php

namespace App\Http\Controllers;

use Dompdf\Dompdf;
use App\Models\ProdiModel;
use Illuminate\Http\Request;
use App\Models\MahasiswaModel;
use App\Models\PembimbingModel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MahasiswaController extends Controller
{
    public function formTambah()
    {
        // Mengambil data pembimbing
        $data_pembimbing = PembimbingModel::all();
        $data_prodi = ProdiModel::all();

        return view('pages.mahasiswa.form_isian_mahasiswa', compact('data_pembimbing', 'data_prodi'));
    }

    public function formUbah($mhs_id)
    {
        $data_mahasiswa = MahasiswaModel::findOrFail($mhs_id);

        // Mengambil data pembimbing
        $data_pembimbing = PembimbingModel::all();
        $data_prodi = ProdiModel::all();

        return view('pages.mahasiswa.form_isian_mahasiswa', compact('data_mahasiswa', 'data_pembimbing', 'data_prodi'));
    }

    public function index()
    {
        // Mengambil data mahasiswa dan mengurutkannya berdasarkan ID terakhir
        $data = MahasiswaModel::select('mahasiswa.*', 'prodi.prodi_id', 'prodi.prodi_nama', 'pembimbing.pembimbing_nama')
            ->join('prodi', 'mahasiswa.mhs_prodi_id', '=', 'prodi.prodi_id')
            ->join('pembimbing', 'mahasiswa.mhs_pembimbing_id', '=', 'pembimbing.pembimbing_id')
            ->whereNull('mahasiswa.deleted_at') // Menambahkan kondisi untuk memastikan deleted_at adalah NULL
            ->orderBy('mahasiswa.mhs_id', 'desc')
            ->get();

        // Mengambil data pembimbing
        $data_pembimbing = PembimbingModel::all();
        $data_prodi = ProdiModel::all();

        return view('pages.mahasiswa.main_mahasiswa', compact('data', 'data_pembimbing', 'data_prodi'));
    }

    public function show($id)
    {
        // Mengambil data mahasiswa berdasarkan ID
        $mahasiswa = MahasiswaModel::findOrFail($id);

        // Pastikan mahasiswa tidak terhapus (jika menggunakan soft delete)
        if ($mahasiswa->trashed()) {
            abort(404); // Atau tampilkan halaman 404 jika sudah terhapus
        }

        return view('mahasiswa', compact('mahasiswa'));
    }

    public function tambahData(Request $request)
    {
        // Buat entri baru tanpa validasi
        $mahasiswa = new MahasiswaModel();

        // Set nilai-niali properti berdasarkan input form
        $mahasiswa->mhs_prodi_id = $request->mhs_prodi_id;
        $mahasiswa->mhs_nbi = $request->mhs_nbi;
        $mahasiswa->mhs_nama = $request->mhs_nama;
        $mahasiswa->mhs_tgl_lahir = $request->mhs_tgl_lahir;
        $mahasiswa->mhs_jenis_kelamin = $request->mhs_jenis_kelamin;
        $mahasiswa->mhs_no_hp = $request->mhs_no_hp;
        $mahasiswa->mhs_alamat = $request->mhs_alamat;
        $mahasiswa->mhs_pembimbing_id = $request->mhs_pembimbing_id;

        // Simpan gambar ke direktori public gambar
        if ($request->hasFile('mhs_foto_path')) {
            // Ambil nama file gambar
            $image = $request->file('mhs_foto_path');
            $imageName = $image->getClientOriginalName();

            // Buat direktori jika belum ada
            $directory = public_path('uploads/mahasiswa/'.$request->mhs_nbi);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Pindahkan gambar ke direktori yang baru dibuat
            $image->move($directory, $imageName);
            $mahasiswa->mhs_foto_path = 'uploads/mahasiswa/'.$request->mhs_nbi.'/'.$imageName; // Simpan path gambar ke database
        }

        // Simpan data ke database
        $mahasiswa->save();

        // Set variabel sesi untuk pesan berhasil
        Session::flash('success_insert_mhs', 'Data Mahasiswa Berhasil Disimpan');

        // Kembalikan ke halaman utama atau halaman yang Anda inginkan
        return redirect()->route('mahasiswa');
    }

    public function ubahData(Request $request)
    {

        $updated_mahasiswa = MahasiswaModel::findOrFail($request->mhs_id);

        $updated_mahasiswa->mhs_prodi_id = $request->mhs_prodi_id;
        $updated_mahasiswa->mhs_nbi = $request->mhs_nbi;
        $updated_mahasiswa->mhs_nama = $request->mhs_nama;
        $updated_mahasiswa->mhs_tgl_lahir = $request->mhs_tgl_lahir;
        $updated_mahasiswa->mhs_jenis_kelamin = $request->mhs_jenis_kelamin;
        $updated_mahasiswa->mhs_no_hp = $request->mhs_no_hp;
        $updated_mahasiswa->mhs_alamat = $request->mhs_alamat;
        $updated_mahasiswa->mhs_pembimbing_id = $request->mhs_pembimbing_id;

        // Simpan gambar ke direktori public gambar
        if ($request->hasFile('mhs_foto_path')) {
            // Ambil nama file gambar
            $image = $request->file('mhs_foto_path');
            $imageName = $image->getClientOriginalName();

            // Buat direktori jika belum ada
            $directory = public_path('uploads/mahasiswa/'.$request->mhs_nbi);
            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

            // Pindahkan gambar ke direktori yang baru dibuat
            $image->move($directory, $imageName);
            $updated_mahasiswa->mhs_foto_path = 'uploads/mahasiswa/'.$request->mhs_nbi.'/'.$imageName; // Simpan path gambar ke database
        }

        $updated_mahasiswa->save();

        // Set variabel sesi untuk pesan berhasil
        Session::flash('success_update_mhs', 'Data Mahasiswa Berhasil Diubah');

        // Kembalikan ke halaman utama atau halaman yang Anda inginkan
        return redirect()->route('mahasiswa');
    }

    public function hapusData($mhs_id)
    {
        $mahasiswa = MahasiswaModel::findOrFail($mhs_id);
        $mahasiswa->delete();

        Session::flash('delete_mhs', 'Data Mahasiswa Berhasil Dihapus');
        return redirect()->route('mahasiswa');
    }

    public function getLastNbiByProdiId($prodiId)
    {
        // Cari NBI terakhir berdasarkan prodi_id
        $lastNbi = MahasiswaModel::where('mhs_prodi_id', $prodiId)
            ->orderBy('mhs_nbi', 'desc')
            ->pluck('mhs_nbi')
            ->first();

        // Jika NBI terakhir tidak ditemukan
        if (!$lastNbi) {
            // Ambil prodi_kode berdasarkan prodi_id
            $prodiKode = ProdiModel::where('prodi_id', $prodiId)->value('prodi_kode');

            // Ambil tahun sekarang
            $tahun = date('y');

            // Format NBI sesuai prodi_kode + tahun + 00001
            $newNbi = $prodiKode . $tahun . '00001';
        } else {
            // Dapatkan nomor urut dari NBI terakhir
            $lastNbiNumber = substr($lastNbi, -5);

            // Jika nomor urut melebihi 99999, kembalikan NBI terakhir
            if ($lastNbiNumber >= 99999) {
                return response()->json(['lastNbi' => $lastNbi]);
            }

            // Tambahkan 1 pada nomor urut
            $newNbiNumber = str_pad($lastNbiNumber + 1, 5, '0', STR_PAD_LEFT);

            // Format NBI baru dengan nomor urut yang telah ditambahkan
            $newNbi = substr_replace($lastNbi, $newNbiNumber, -5);
        }

        return response()->json(['lastNbi' => $newNbi]);
    }

    public function formCetakKeteranganKuliah()
    {
        return view('pages.mahasiswa.form_isian_keterangan_kuliah');
    }

    public function cetakKeteranganKuliahPDF(Request $request)
    {
        $data_mahasiswa = MahasiswaModel::select('mahasiswa.*', 'prodi.prodi_id', 'prodi.prodi_nama', 'pembimbing.pembimbing_nama')
            ->join('prodi', 'mahasiswa.mhs_prodi_id', '=', 'prodi.prodi_id')
            ->join('pembimbing', 'mahasiswa.mhs_pembimbing_id', '=', 'pembimbing.pembimbing_id')
            ->where('mahasiswa.mhs_nbi', $request->mhs_nbi) // Mengubah '==' menjadi '='
            ->first(); // Menggunakan first() untuk mendapatkan satu record

        $html = view('pages.mahasiswa.laporan.form_cetak_keterangan_kuliah', compact('data_mahasiswa'))->render();

        $pdf = new Dompdf();
        $pdf->loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->render();

        $output = $pdf->output();
        $filename = 'data-mahasiswa-' . date("Ymd-His") . '.pdf';
        $path = 'pdf/' . $filename;

        Storage::put($path, $output);

        $url = Storage::url($path);

        // return response()->json(['url' => $url]);
        $pdf->stream($filename);
    }

    public function cetakDataPDF() {
        $data_mahasiswa = MahasiswaModel::select('mahasiswa.*', 'prodi.prodi_id', 'prodi.prodi_nama', 'pembimbing.pembimbing_nama')
            ->join('prodi', 'mahasiswa.mhs_prodi_id', '=', 'prodi.prodi_id')
            ->join('pembimbing', 'mahasiswa.mhs_pembimbing_id', '=', 'pembimbing.pembimbing_id')
            ->whereNull('mahasiswa.deleted_at')
            ->orderBy('mahasiswa.mhs_prodi_id', 'asc')
            ->get();

        $filename = 'data-mahasiswa-'.date("Ymd-His").'.pdf';

        $pdf = new Dompdf();
        $pdf->loadHTML(view('pages.mahasiswa.laporan.form_cetak_pdf_mahasiswa', compact('data_mahasiswa'))->render());
        $pdf->setPaper('A4', 'landscape');
        $pdf->render();
        $pdf->stream($filename);
    }

    public function cetakDataExcel()
    {
        // Query untuk mendapatkan data mahasiswa
        $data_mahasiswa = MahasiswaModel::select('mahasiswa.*', 'prodi.prodi_id', 'prodi.prodi_nama', 'pembimbing.pembimbing_nama')
        ->join('prodi', 'mahasiswa.mhs_prodi_id', '=', 'prodi.prodi_id')
        ->join('pembimbing', 'mahasiswa.mhs_pembimbing_id', '=', 'pembimbing.pembimbing_id')
        ->whereNull('mahasiswa.deleted_at')
        ->orderBy('mahasiswa.mhs_prodi_id', 'asc')
        ->get();

        // Buat objek Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Nama');
        $sheet->setCellValue('C1', 'NIM');
        $sheet->setCellValue('D1', 'Jenis Kelamin');
        $sheet->setCellValue('E1', 'Tgl Lahir');
        $sheet->setCellValue('F1', 'Alamat');
        $sheet->setCellValue('G1', 'Prodi');
        $sheet->setCellValue('H1', 'Pembimbing');

        // Data mahasiswa
        $row = 2;
        $number = 1;
        foreach ($data_mahasiswa as $mahasiswa) {
            $sheet->setCellValue('A' . $row, $number);
            $sheet->setCellValue('B' . $row, $mahasiswa->mhs_nama);
            $sheet->setCellValue('C' . $row, $mahasiswa->mhs_nbi);
            $sheet->setCellValue('D' . $row, $mahasiswa->mhs_jenis_kelamin);
            $sheet->setCellValue('E' . $row, $mahasiswa->mhs_tgl_lahir);
            $sheet->setCellValue('F' . $row, $mahasiswa->mhs_alamat);
            $sheet->setCellValue('G' . $row, $mahasiswa->prodi_nama);
            $sheet->setCellValue('H' . $row, $mahasiswa->pembimbing_nama);
            $row++;
            $number++;
        }

        // Buat writer Xlsx
        $writer = new Xlsx($spreadsheet);

        // Simpan file sementara di penyimpanan sementara
        $tempFilePath = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFilePath);

        // Atur header respons untuk file Excel
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        // Ambil tanggal saat ini untuk nama file
        $filename = 'data-mahasiswa-'.date("Ymd-His").'.xlsx';

        // Kembalikan respons untuk mendownload file Excel
        return Response::download($tempFilePath, $filename, $headers)->deleteFileAfterSend(true);
    }

}
