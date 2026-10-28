<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| DAFTAR ALAMAT HALAMAN (TAHAP TAMPILAN)
|--------------------------------------------------------------------------
| Route::view(alamat, nama_file_view, data_yang_dikirim)
| Nama file view ditulis tanpa ".blade.php" dan memakai titik sebagai folder.
| Contoh: 'admin.dashboard' = resources/views/admin/dashboard.blade.php
|
| Menu yang halamannya belum dibuat memakai view 'placeholder' dulu.
| Kalau halaman aslinya sudah ada, ganti 'placeholder' dengan nama view-nya.
*/

// Alamat awal langsung diarahkan ke halaman login
Route::redirect('/', '/login');

// ===== LOGIN dan LOGOUT =====
Route::view('/login', 'auth.login');

// SEMENTARA: belum memeriksa password dan database.
// Hanya mengarahkan sesuai username, supaya tampilan tiap peran bisa dilihat.
Route::post('/login', function (Request $request) {
    $username = strtolower(trim($request->input('username', '')));

    return match ($username) {
        'admin'    => redirect('/admin/dashboard'),
        'petugas'  => redirect('/petugas/dashboard'),
        'pimpinan' => redirect('/pimpinan/dashboard'),
        default    => back()
            ->withErrors(['username' => 'Untuk tahap tampilan, isi username: admin, petugas, atau pimpinan.'])
            ->withInput(),
    };
});

Route::post('/logout', fn () => redirect('/login'));

// ===== ADMIN =====
Route::view('/admin/dashboard', 'admin.dashboard',    ['role' => 'admin']);
Route::view('/admin/pengguna',  'placeholder',        ['role' => 'admin', 'judul' => 'Pengguna']);
Route::view('/admin/wilayah',   'placeholder',        ['role' => 'admin', 'judul' => 'Wilayah']);
Route::view('/admin/periode',   'placeholder',        ['role' => 'admin', 'judul' => 'Periode Sensus']);
Route::view('/admin/master',    'placeholder',        ['role' => 'admin', 'judul' => 'Data Master']);
Route::view('/admin/konflik',   'placeholder',        ['role' => 'admin', 'judul' => 'Konflik Sinkronisasi']);
Route::view('/admin/replikasi', 'placeholder',        ['role' => 'admin', 'judul' => 'Monitor Replikasi']);

// ===== PETUGAS =====
Route::view('/petugas/dashboard',       'placeholder', ['role' => 'petugas', 'judul' => 'Dashboard Petugas']);
Route::view('/petugas/keluarga',        'placeholder', ['role' => 'petugas', 'judul' => 'Daftar Keluarga']);
Route::view('/petugas/keluarga/tambah', 'placeholder', ['role' => 'petugas', 'judul' => 'Tambah Keluarga']);
Route::view('/petugas/sinkron',         'placeholder', ['role' => 'petugas', 'judul' => 'Status Sinkron']);

// ===== PIMPINAN =====
Route::view('/pimpinan/dashboard',     'placeholder', ['role' => 'pimpinan', 'judul' => 'Dashboard Pimpinan']);
Route::view('/pimpinan/statistik',     'placeholder', ['role' => 'pimpinan', 'judul' => 'Statistik']);
Route::view('/pimpinan/layak-bantuan', 'placeholder', ['role' => 'pimpinan', 'judul' => 'Layak Bantuan']);
Route::view('/pimpinan/laporan',       'placeholder', ['role' => 'pimpinan', 'judul' => 'Laporan']);