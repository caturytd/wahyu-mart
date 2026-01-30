<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\TransaksiBarangMasuk;
use App\Livewire\TransaksiBarangKeluar;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Livewire\EditTransaksiBarangMasuk;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PemasokController;
use App\Livewire\EditTransaksiBarangKeluar;
use App\Http\Controllers\BarangBatchController;
use App\Http\Controllers\BarangMasukController;
use App\Http\Controllers\BatchBarangController;
use App\Http\Controllers\BarangKeluarController;
use App\Http\Controllers\KategoriBarangController;
use App\Http\Controllers\PengaturanTokoController;
use App\Http\Controllers\LaporanBarangMasukController;
use App\Http\Controllers\LaporanBarangKeluarController;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::group(['middleware' => ['auth']], function (){
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::resource('/barang', BarangController::class);
    Route::get('/barang/laporan/pdf', [BarangController::class, 'laporan'])->name('barang.laporan');

Route::resource('barang-batch', BarangBatchController::class);
// Route::get('barang-batch/export', [BarangBatchController::class, 'export'])->name('barang-batch.export');
    
    Route::resource('/pemasok', PemasokController::class);
    Route::resource('/kategori-barang', KategoriBarangController::class);

    Route::get('/barang-keluar', [BarangKeluarController::class, 'index'])->name('barang-keluar.index');
    Route::get('/barang-keluar/show/{id}', [BarangKeluarController::class, 'show'])->name('barang-keluar.show');
    Route::delete('/barang-keluar/{id}', [BarangKeluarController::class, 'destroy'])->name('barang-keluar.destroy');
    Route::get('/barang-keluar/create', TransaksiBarangKeluar::class)->name('barang-keluar.create');
    Route::get('/barang-keluar/edit/{id}', EditTransaksiBarangKeluar::class)->name('barang-keluar.edit');
    Route::get('/barang-keluar/pdf/{id}', [BarangKeluarController::class, 'donwloadPdf'])->name('barang-keluar.pdf');
   
    Route::get('/barang-masuk', [BarangMasukController::class, 'index'])->name('barang-masuk.index');
    Route::get('/barang-masuk/show/{id}', [BarangMasukController::class, 'show'])->name('barang-masuk.show');
    Route::delete('/barang-masuk/{id}', [BarangMasukController::class, 'destroy'])->name('barang-masuk.destroy');
    Route::get('/barang-masuk/create', TransaksiBarangMasuk::class)->name('barang-masuk.create');
    Route::get('/barang-masuk/edit/{id}', EditTransaksiBarangMasuk::class)->name('barang-masuk.edit');
    Route::get('/barang-masuk/pdf/{id}', [BarangMasukController::class, 'downloadPdf'])->name('barang-masuk.pdf');

    Route::get('/laporan-barang-masuk', [LaporanBarangMasukController::class, 'index'])->name('laporan.barang-masuk.index');
    Route::get('/laporan-barang-masuk/pdf', [LaporanBarangMasukController::class, 'exportPdf'])->name('laporan.barang-masuk.pdf');
    Route::get('/laporan-barang-keluar', [LaporanBarangKeluarController::class, 'index'])->name('laporan.barang-keluar.index');
    Route::get('/laporan-barang-keluar/{id}/detail', [LaporanBarangKeluarController::class, 'detail'])->name('laporan.barang-keluar.detail');
    Route::get('/laporan-barang-keluar/pdf', [LaporanBarangKeluarController::class, 'exportPdf'])->name('laporan.barang-keluar.pdf');

    Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.update');

    Route::get('/pengaturan-toko', [PengaturanTokoController::class, 'index'])->name('pengaturan-toko.index');
    Route::post('/pengaturan-toko', [PengaturanTokoController::class, 'store'])->name('pengaturan-toko.store');

});
