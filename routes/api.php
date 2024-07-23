<?php

use App\Http\Controllers\LaporanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\BukuKategoriController;
use App\Http\Controllers\PengembalianController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('/dashboard')->group(function () {
        Route::get('/grafik-bar-netsales', [DashboardController::class, 'getDataGrafikBarNetsales']);
    });

    Route::prefix('/buku')->group(function () {
        // Route proses CRUD
        Route::post('/list', [BukuController::class, 'bukuList']);
        Route::get('/detail/{buku_id}', [BukuController::class, 'bukuDataDetail']);
        Route::post('/create', [BukuController::class, 'bukuCreate']);
        Route::put('/update', [BukuController::class, 'bukuUpdate']);
        Route::delete('/delete/{buku_id}', [BukuController::class, 'bukuDelete']);
        Route::get('/cetak-list-pdf/', [BukuController::class, 'cetakListBukuPDF']);
        Route::get('/cetak-list-xls/', [BukuController::class, 'cetakListBukuExcel']);
    });

    Route::prefix('/bukuKategori')->group(function () {
        // Route proses CRUD
        Route::post('/list', [BukuKategoriController::class, 'bukuKategoriList']);
        Route::get('/detail/{kategori_id}', [BukuKategoriController::class, 'bukuKategoriDataDetail']);
        Route::post('/create', [BukuKategoriController::class, 'bukuKategoriCreate']);
        Route::put('/update', [BukuKategoriController::class, 'bukuKategoriUpdate']);
        Route::delete('/delete/{kategori_id}', [BukuKategoriController::class, 'bukuKategoriDelete']);
        Route::get('/cetak-list-pdf/', [BukuKategoriController::class, 'cetakListBukuKategoriPDF']);
        Route::get('/cetak-list-xls/', [BukuKategoriController::class, 'cetakListBukuKategoriExcel']);
    });

    Route::prefix('/peminjaman')->group(function () {
        // Route proses CRUD
        Route::post('/list', [PeminjamanController::class, 'peminjamanList']);
        Route::get('/detail/{peminjaman_id}', [PeminjamanController::class, 'peminjamanDataDetail']);
        Route::post('/create', [PeminjamanController::class, 'peminjamanCreate']);
        Route::put('/update', [PeminjamanController::class, 'peminjamanUpdate']);
        Route::delete('/delete/{peminjaman_id}', [PeminjamanController::class, 'peminjamanDelete']);
        Route::get('/cetak-list-pdf/', [PeminjamanController::class, 'cetakListPeminjamanPDF']);
        Route::get('/cetak-list-xls/', [PeminjamanController::class, 'cetakListPeminjamanExcel']);
        Route::get('/cetak-faktur-pdf/{peminjaman_id}', [PeminjamanController::class, 'cetakFakturPDF']);
    });

    Route::prefix('/pengembalian')->group(function () {
        // Fungsi load data
        Route::post('/pinjaman_list', [PengembalianController::class, 'pinjamanList']);
        Route::post('/buku_by_pinjaman_list', [PengembalianController::class, 'bukuByPinjamanIdList']);

        // Fungsi core
        Route::post('/list', [PengembalianController::class, 'pengembalianList']);
        Route::get('/detail/{pengembalian_id}', [PengembalianController::class, 'pengembalianDataDetail']);
        Route::post('/create', [PengembalianController::class, 'pengembalianCreate']);
        Route::put('/update', [PengembalianController::class, 'pengembalianUpdate']);
        Route::delete('/delete/{pengembalian_id}', [PengembalianController::class, 'pengembalianDelete']);
        Route::get('/cetak-list-pdf/', [PengembalianController::class, 'cetakListPengembalianPDF']);
        Route::get('/cetak-list-xls/', [PengembalianController::class, 'cetakListPengembalianExcel']);
        Route::get('/cetak-faktur-pdf/{pengembalian_id}', [PengembalianController::class, 'cetakFakturPDF']);
    });

    Route::prefix('/laporan')->group(function () {
        Route::prefix('/peminjaman')->group(function () {
            Route::post('/', [LaporanController::class, 'laporanPinjamanList']);
            Route::post('/cetak-list-pdf/', [LaporanController::class, 'cetakListPeminjamanPDF']);
            Route::post('/cetak-list-xls/', [LaporanController::class, 'cetakListPeminjamanExcel']);
        });

        Route::prefix('/pengembalian')->group(function () {
            Route::post('/', [LaporanController::class, 'laporanPengembalianList']);
            Route::post('/cetak-list-pdf/', [LaporanController::class, 'cetakListPengembalianPDF']);
            Route::post('/cetak-list-xls/', [LaporanController::class, 'cetakListPengembalianExcel']);
        });
    });


});
