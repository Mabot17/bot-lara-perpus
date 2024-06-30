<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\BukuKategoriController;
use App\Http\Controllers\PengembalianController;

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

Route::prefix('/buku')->group(function () {
    // Route API Handle form buku
    Route::get('/', [BukuController::class, 'index'])->name('buku');
    Route::get('/tambah', [BukuController::class, 'formTambah'])->name('buku.tambah');
    Route::get('/ubah/{buku_id}', [BukuController::class, 'formUbah'])->name('buku.ubah');
});

Route::prefix('/bukuKategori')->group(function () {
    // Route API Handle form buku
    Route::get('/', [BukuKategoriController::class, 'index'])->name('bukuKategori');
    Route::get('/tambah', [BukuKategoriController::class, 'formTambah'])->name('bukuKategori.tambah');
    Route::get('/ubah/{kategori_id}', [BukuKategoriController::class, 'formUbah'])->name('bukuKategori.ubah');
});

Route::prefix('/peminjaman')->group(function () {
    // Route API Handle form buku
    Route::get('/', [PeminjamanController::class, 'index'])->name('peminjaman');
    Route::get('/tambah', [PeminjamanController::class, 'formTambah'])->name('peminjaman.tambah');
    Route::get('/ubah/{kategori_id}', [PeminjamanController::class, 'formUbah'])->name('peminjaman.ubah');
});

Route::prefix('/pengembalian')->group(function () {
    // Route API Handle form buku
    Route::get('/', [PengembalianController::class, 'index'])->name('pengembalian');
    Route::get('/tambah', [PengembalianController::class, 'formTambah'])->name('pengembalian.tambah');
    Route::get('/ubah/{kategori_id}', [PengembalianController::class, 'formUbah'])->name('pengembalian.ubah');
});
