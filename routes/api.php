<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PenjualanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProdukKategoriController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('/dashboard')->group(function () {
        Route::get('/grafik-bar-netsales', [DashboardController::class, 'getDataGrafikBarNetsales']);
    });


    Route::prefix('/produk')->group(function () {
        // Route proses CRUD
        Route::post('/list', [ProdukController::class, 'produkList']);
        Route::get('/detail/{prodi_id}', [ProdukController::class, 'produkDataDetail']);
        Route::post('/create', [ProdukController::class, 'produkCreate']);
        Route::put('/update', [ProdukController::class, 'produkUpdate']);
        Route::delete('/delete/{produk_id}', [ProdukController::class, 'produkDelete']);
        Route::get('/cetak-list-pdf/', [ProdukController::class, 'cetakListProdukPDF']);
        Route::get('/cetak-list-xls/', [ProdukController::class, 'cetakListProdukExcel']);
    });

    Route::prefix('/produkKategori')->group(function () {
        // Route proses CRUD
        Route::post('/list', [ProdukKategoriController::class, 'produkKategoriList']);
        Route::get('/detail/{kategori_id}', [ProdukKategoriController::class, 'produkKategoriDataDetail']);
        Route::post('/create', [ProdukKategoriController::class, 'produkKategoriCreate']);
        Route::put('/update', [ProdukKategoriController::class, 'produkKategoriUpdate']);
        Route::delete('/delete/{kategori_id}', [ProdukKategoriController::class, 'produkKategoriDelete']);
        // Route::get('/view-cetak-keterangan', [MahasiswaController::class, 'formCetakKeteranganKuliah'])->name('mahasiswa.form-cetak-keterangan-kuliah');
        Route::get('/cetak-list-pdf/', [ProdukKategoriController::class, 'cetakListProdukKategoriPDF']);
        Route::get('/cetak-list-xls/', [ProdukKategoriController::class, 'cetakListProdukKategoriExcel']);
    });

    Route::prefix('/penjualan')->group(function () {
        // Route proses CRUD
        Route::post('/list', [PenjualanController::class, 'penjualanList']);
        Route::get('/detail/{penjualan_id}', [PenjualanController::class, 'penjualanDataDetail']);
        Route::post('/create', [PenjualanController::class, 'penjualanCreate']);
        Route::put('/update', [PenjualanController::class, 'penjualanUpdate']);
        Route::delete('/delete/{penjualan_id}', [PenjualanController::class, 'penjualanDelete']);
        Route::get('/cetak-list-pdf/', [PenjualanController::class, 'cetakListPenjualanPDF']);
        Route::get('/cetak-list-xls/', [PenjualanController::class, 'cetakListPenjualanExcel']);
        Route::get('/cetak-faktur-pdf/{penjualan_id}', [PenjualanController::class, 'cetakFakturPDF']);
    });

});
