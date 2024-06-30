<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PeminjamanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\BukuKategoriController;

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
        // Route::get('/view-cetak-keterangan', [MahasiswaController::class, 'formCetakKeteranganKuliah'])->name('mahasiswa.form-cetak-keterangan-kuliah');
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

});
