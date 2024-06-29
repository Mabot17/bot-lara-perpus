<?php

use App\Http\Controllers\PenjualanController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProdukKategoriController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [MainController::class, 'index']);
Route::get('/login', [MainController::class, 'index']);
Route::get('/register', [MainController::class, 'register']);


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::prefix('/produk')->group(function () {
    // Route API Handle form produk
    Route::get('/', [ProdukController::class, 'index'])->name('produk');
    Route::get('/tambah', [ProdukController::class, 'formTambah'])->name('produk.tambah');
    Route::get('/ubah/{produk_id}', [ProdukController::class, 'formUbah'])->name('produk.ubah');
});

Route::prefix('/produkKategori')->group(function () {
    // Route API Handle form produk
    Route::get('/', [ProdukKategoriController::class, 'index'])->name('produkKategori');
    Route::get('/tambah', [ProdukKategoriController::class, 'formTambah'])->name('produkKategori.tambah');
    Route::get('/ubah/{kategori_id}', [ProdukKategoriController::class, 'formUbah'])->name('produkKategori.ubah');
});

Route::prefix('/penjualan')->group(function () {
    // Route API Handle form produk
    Route::get('/', [PenjualanController::class, 'index'])->name('penjualan');
    Route::get('/tambah', [PenjualanController::class, 'formTambah'])->name('penjualan.tambah');
    Route::get('/ubah/{kategori_id}', [PenjualanController::class, 'formUbah'])->name('penjualan.ubah');
});
