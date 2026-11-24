<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\DataMasterController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Admin\PenilaianController;
use App\Http\Controllers\Admin\PeriodeController;
use App\Http\Controllers\Admin\ReplikasiController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Petugas\DashboardController as PetugasDashboard;
use App\Http\Controllers\Petugas\KeluargaController as PetugasKeluarga;
use App\Http\Controllers\Petugas\PendataanController as PetugasPendataan;
use App\Http\Controllers\Pimpinan\DashboardController as PimpinanDashboard;
use App\Http\Controllers\Pimpinan\LaporanController as PimpinanLaporan;
use App\Http\Controllers\Pimpinan\LayakBantuanController as PimpinanLayakBantuan;
use App\Http\Controllers\Pimpinan\StatistikController as PimpinanStatistik;
use App\Http\Middleware\PeranMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| DAFTAR ALAMAT HALAMAN
|--------------------------------------------------------------------------
| Route::get(alamat, [NamaController::class, 'namaMetode'])  = halaman dengan data dari database
| Route::view(alamat, 'nama.view')                           = halaman tampilan saja (belum ada data)
| 'auth' memastikan pengguna sudah login, PeranMiddleware membatasi menurut peran.
*/

Route::redirect('/', '/login');

// ===== LOGIN dan LOGOUT =====
Route::get('/login', [AuthController::class, 'form'])->name('login');
Route::post('/login', [AuthController::class, 'masuk']);
Route::post('/logout', [AuthController::class, 'keluar']);

// ===== ADMIN =====
Route::middleware(['auth', PeranMiddleware::class.':admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index']);
    Route::get('/pengguna', [PenggunaController::class, 'index']);
    Route::post('/pengguna', [PenggunaController::class, 'store']);
    Route::put('/pengguna/{user}', [PenggunaController::class, 'update']);
    Route::patch('/pengguna/{user}/aktif', [PenggunaController::class, 'toggleAktif']);
    Route::put('/pengguna/{user}/password', [PenggunaController::class, 'resetPassword']);
    Route::get('/wilayah', [WilayahController::class, 'index']);
    Route::post('/wilayah/dusun', [WilayahController::class, 'storeDusun']);
    Route::put('/wilayah/dusun/{dusun}', [WilayahController::class, 'updateDusun']);
    Route::delete('/wilayah/dusun/{dusun}', [WilayahController::class, 'deleteDusun']);
    Route::post('/wilayah/rw', [WilayahController::class, 'storeRw']);
    Route::put('/wilayah/rw/{rw}', [WilayahController::class, 'updateRw']);
    Route::delete('/wilayah/rw/{rw}', [WilayahController::class, 'deleteRw']);
    Route::post('/wilayah/rt', [WilayahController::class, 'storeRt']);
    Route::put('/wilayah/rt/{rt}', [WilayahController::class, 'updateRt']);
    Route::delete('/wilayah/rt/{rt}', [WilayahController::class, 'deleteRt']);
    Route::get('/periode', [PeriodeController::class, 'index']);
    Route::post('/periode', [PeriodeController::class, 'store']);
    Route::put('/periode/{periode}', [PeriodeController::class, 'update']);
    Route::get('/master', [DataMasterController::class, 'index']);
    Route::post('/master/{jenis}', [DataMasterController::class, 'store']);
    Route::put('/master/{jenis}/{id}', [DataMasterController::class, 'update']);
    Route::delete('/master/{jenis}/{id}', [DataMasterController::class, 'destroy']);
    Route::get('/penilaian', [PenilaianController::class, 'index']);
    Route::get('/replikasi', [ReplikasiController::class, 'index']);
});

// ===== PETUGAS =====
Route::middleware(['auth', PeranMiddleware::class.':petugas'])->prefix('petugas')->group(function () {
    Route::get('/dashboard', [PetugasDashboard::class, 'index']);
    Route::get('/keluarga', [PetugasKeluarga::class, 'index']);
    Route::get('/keluarga/tambah', [PetugasKeluarga::class, 'create']);
    Route::post('/keluarga/tambah', [PetugasKeluarga::class, 'store']);
    Route::get('/keluarga/{keluarga}/ubah', [PetugasKeluarga::class, 'edit']);
    Route::put('/keluarga/{keluarga}', [PetugasKeluarga::class, 'update']);
    Route::get('/keluarga/{keluarga}', [PetugasKeluarga::class, 'show']);
    Route::get('/pendataan', [PetugasPendataan::class, 'create']);
    Route::post('/pendataan', [PetugasPendataan::class, 'store']);
});

// ===== PIMPINAN =====
Route::middleware(['auth', PeranMiddleware::class.':pimpinan'])->prefix('pimpinan')->group(function () {
    Route::get('/dashboard', [PimpinanDashboard::class, 'index']);
    Route::get('/statistik', [PimpinanStatistik::class, 'index']);
    Route::get('/layak-bantuan', [PimpinanLayakBantuan::class, 'index']);
    Route::get('/layak-bantuan/{pendataan}', [PimpinanLayakBantuan::class, 'show']);
    Route::get('/laporan', [PimpinanLaporan::class, 'index']);
    Route::get('/laporan/ekspor', [PimpinanLaporan::class, 'ekspor']);
});
