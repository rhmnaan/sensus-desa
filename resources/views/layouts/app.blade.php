<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Beranda') · Sensus Ekonomi Desa</title>
    <style>
    /* ===== WARNA (ubah di sini kalau mau ganti tema) ===== */
    :root {
        --hijau: #0f766e;
        --hijau-tua: #134e4a;
        --hijau-muda: #ccfbf1;
        --bg: #f1f5f4;
        --kartu: #ffffff;
        --teks: #1f2937;
        --abu: #6b7280;
        --garis: #e5e7eb;
        --ok: #15803d;
        --ok-bg: #dcfce7;
        --warn: #b45309;
        --warn-bg: #fef3c7;
        --bahaya: #b91c1c;
        --bahaya-bg: #fee2e2;
    }

    * {
        box-sizing: border-box
    }

    body {
        margin: 0;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
        background: var(--bg);
        color: var(--teks);
        font-size: 15px;
        line-height: 1.5
    }

    /* ===== KERANGKA ===== */
    .app {
        display: flex;
        min-height: 100vh
    }

    .utama {
        flex: 1;
        min-width: 0
    }

    .isi {
        padding: 24px
    }

    /* ===== SIDEBAR ===== */
    .sidebar {
        width: 250px;
        background: var(--hijau-tua);
        color: #fff;
        display: flex;
        flex-direction: column;
        position: sticky;
        top: 0;
        height: 100vh;
        flex-shrink: 0
    }

    .brand {
        display: flex;
        gap: 12px;
        align-items: center;
        padding: 20px 18px
    }

    .brand-logo {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--hijau);
        display: grid;
        place-items: center;
        font-weight: 700
    }

    .brand-nama {
        font-weight: 700
    }

    .brand-sub {
        font-size: 12px;
        opacity: .7
    }

    .peran {
        margin: 0 18px 12px;
        padding: 6px 10px;
        background: rgba(255, 255, 255, .1);
        border-radius: 8px;
        font-size: 12px;
        text-align: center
    }

    .menu {
        flex: 1;
        overflow-y: auto;
        padding: 4px 10px
    }

    .menu a {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        margin-bottom: 2px;
        border-radius: 8px;
        color: #d1fae5;
        text-decoration: none
    }

    .menu a:hover {
        background: rgba(255, 255, 255, .08)
    }

    .menu a.aktif {
        background: var(--hijau);
        color: #fff;
        font-weight: 600
    }

    .titik {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
        opacity: .6
    }

    .sidebar-bawah {
        padding: 14px 18px;
        font-size: 12px;
        opacity: .6
    }

    /* ===== HEADER ATAS ===== */
    .topbar {
        display: flex;
        align-items: center;
        gap: 14px;
        background: var(--kartu);
        border-bottom: 1px solid var(--garis);
        padding: 12px 24px;
        position: sticky;
        top: 0;
        z-index: 5
    }

    .topbar h1 {
        font-size: 18px;
        margin: 0;
        flex: 1
    }

    .burger {
        display: none;
        background: none;
        border: 0;
        font-size: 22px;
        cursor: pointer
    }

    .pengguna {
        display: flex;
        align-items: center;
        gap: 10px
    }

    .pengguna form {
        margin: 0
    }

    .avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: var(--hijau-muda);
        color: var(--hijau-tua);
        display: grid;
        place-items: center;
        font-weight: 700;
        font-size: 13px
    }

    /* ===== KOMPONEN UMUM (dipakai semua halaman) ===== */
    .kartu {
        background: var(--kartu);
        border: 1px solid var(--garis);
        border-radius: 12px;
        padding: 18px
    }

    .kartu h2 {
        font-size: 15px;
        margin: 0 0 14px
    }

    .grid {
        display: grid;
        gap: 16px;
        margin-bottom: 16px
    }

    .grid-4 {
        grid-template-columns: repeat(4, 1fr)
    }

    .grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px
    }

    .grid-2 {
        grid-template-columns: 1fr 1fr
    }

    .stat .label {
        color: var(--abu);
        font-size: 13px
    }

    .stat .angka {
        font-size: 28px;
        font-weight: 700;
        line-height: 1.3
    }

    .stat .ket {
        font-size: 12px;
        color: var(--abu)
    }

    .baris-bar {
        margin-bottom: 14px
    }

    .baris-bar .judul {
        display: flex;
        justify-content: space-between;
        margin-bottom: 6px;
        font-size: 14px
    }

    .bar {
        height: 10px;
        background: var(--garis);
        border-radius: 99px;
        overflow: hidden
    }

    .bar span {
        display: block;
        height: 100%;
        background: var(--hijau)
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px
    }

    th,
    td {
        text-align: left;
        padding: 10px 8px;
        border-bottom: 1px solid var(--garis)
    }

    th {
        color: var(--abu);
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em
    }

    .badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 99px;
        font-size: 12px;
        font-weight: 600
    }

    .b-ok {
        background: var(--ok-bg);
        color: var(--ok)
    }

    .b-warn {
        background: var(--warn-bg);
        color: var(--warn)
    }

    .b-bahaya {
        background: var(--bahaya-bg);
        color: var(--bahaya)
    }

    .btn {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 8px;
        border: 0;
        background: var(--hijau);
        color: #fff;
        font-size: 14px;
        cursor: pointer;
        text-decoration: none
    }

    .btn-garis {
        background: transparent;
        color: var(--teks);
        border: 1px solid var(--garis)
    }

    .info {
        background: var(--warn-bg);
        color: var(--warn);
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 16px;
        font-size: 13px
    }

    .kosong {
        text-align: center;
        padding: 40px 10px;
        color: var(--abu)
    }

    .tirai {
        display: none
    }

    /* ===== FORM, LANGKAH, PENCARIAN (dipakai halaman petugas) ===== */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px
    }

    .form-grid.dua {
        grid-template-columns: repeat(2, 1fr)
    }

    .field label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 5px
    }

    .field input,
    .field select,
    .field textarea,
    .cari input,
    .cari select {
        width: 100%;
        padding: 9px 11px;
        border: 1px solid var(--garis);
        border-radius: 8px;
        font: inherit;
        background: #fff
    }

    .field input:focus,
    .field select:focus,
    .field textarea:focus {
        outline: 2px solid var(--hijau);
        border-color: transparent
    }

    .field.lebar {
        grid-column: 1/-1
    }

    .centang {
        display: flex;
        gap: 18px;
        align-items: center;
        padding-top: 26px
    }

    .centang label {
        display: flex;
        gap: 6px;
        align-items: center;
        font-weight: 400;
        margin: 0
    }

    .centang input {
        width: auto
    }

    .kartu-sub {
        border: 1px solid var(--garis);
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 12px;
        background: #fafcfb
    }

    .kartu-sub .kepala {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        font-weight: 600;
        font-size: 14px
    }

    .btn-bahaya {
        background: var(--bahaya-bg);
        color: var(--bahaya)
    }

    .btn-kecil {
        padding: 5px 10px;
        font-size: 13px
    }

    .langkah {
        display: flex;
        gap: 8px;
        margin-bottom: 16px;
        flex-wrap: wrap
    }

    .langkah button {
        flex: 1;
        min-width: 120px;
        padding: 10px;
        border: 1px solid var(--garis);
        border-radius: 8px;
        background: #fff;
        font: inherit;
        cursor: pointer;
        color: var(--abu)
    }

    .langkah button.aktif {
        background: var(--hijau);
        border-color: var(--hijau);
        color: #fff;
        font-weight: 600
    }

    .panel {
        display: none
    }

    .panel.tampil {
        display: block
    }

    .aksi-bar {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-top: 18px
    }

    .cari {
        display: flex;
        gap: 10px;
        margin-bottom: 14px;
        flex-wrap: wrap
    }

    .cari input {
        flex: 2;
        min-width: 200px
    }

    .cari select {
        flex: 1;
        min-width: 140px
    }

    .tabel-geser {
        overflow-x: auto
    }

    .ket-kecil {
        color: var(--abu);
        font-size: 12px
    }

    .status-koneksi {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap
    }

    .lampu {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--ok)
    }

    .lampu.mati {
        background: var(--bahaya)
    }

    .judul-halaman {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        flex-wrap: wrap
    }

    /* ===== HP DAN TABLET ===== */
    @media (max-width:1000px) {
        .grid-4 {
            grid-template-columns: repeat(2, 1fr)
        }

        .grid-3 {
            grid-template-columns: repeat(2, 1fr)
        }

        .grid-2 {
            grid-template-columns: 1fr
        }

        .form-grid {
            grid-template-columns: repeat(2, 1fr)
        }
    }

    @media (max-width:640px) {

        .grid-3 {
            grid-template-columns: 1fr
        }

        .form-grid,
        .form-grid.dua {
            grid-template-columns: 1fr
        }

        .aksi-bar {
            flex-direction: column
        }
    }

    @media (max-width:900px) {
        .sidebar {
            position: fixed;
            left: -260px;
            z-index: 20;
            transition: left .2s
        }

        body.menu-buka .sidebar {
            left: 0
        }

        body.menu-buka .tirai {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .4);
            z-index: 15
        }

        .burger {
            display: block
        }

        .nama-user {
            display: none
        }

        .isi {
            padding: 16px
        }
    }
    </style>
</head>

<body>

    @php
    // Ikuti peran akun yang sedang login agar menu sesuai dengan hak akses.
    $role = $role ?? auth()->user()?->canonicalRole() ?? 'admin';

    // ===== DAFTAR MENU PER PERAN (ubah/tambah menu cukup di sini) =====
    $semuaMenu = [
    'admin' => [
    ['nama' => 'Dashboard', 'url' => '/admin/dashboard'],
    ['nama' => 'Pengguna', 'url' => '/admin/pengguna'],
    ['nama' => 'Wilayah', 'url' => '/admin/wilayah'],
    ['nama' => 'Periode Sensus', 'url' => '/admin/periode'],
    ['nama' => 'Data Master', 'url' => '/admin/master'],
    ['nama' => 'Penilaian', 'url' => '/admin/penilaian'],
    ['nama' => 'Monitor Replikasi', 'url' => '/admin/replikasi'],
    ],
    'petugas' => [
    ['nama' => 'Dashboard', 'url' => '/petugas/dashboard'],
    ['nama' => 'Daftar Keluarga', 'url' => '/petugas/keluarga'],
    ['nama' => 'Tambah Keluarga', 'url' => '/petugas/keluarga/tambah'],
    ['nama' => 'Form Pendataan', 'url' => '/petugas/pendataan'],
    ],
    'pimpinan' => [
    ['nama' => 'Dashboard', 'url' => '/pimpinan/dashboard'],
    ['nama' => 'Statistik', 'url' => '/pimpinan/statistik'],
    ['nama' => 'Layak Bantuan', 'url' => '/pimpinan/layak-bantuan'],
    ['nama' => 'Laporan', 'url' => '/pimpinan/laporan'],
    ],
    ];
    $menus = $semuaMenu[$role];

    $labelPeran = ['admin' => 'Administrator', 'petugas' => 'Petugas Lapangan', 'pimpinan' => 'Pimpinan'][$role];
    $namaUser = auth()->user()?->nama ?? ['admin' => 'Admin Desa', 'petugas' => 'Petugas 1', 'pimpinan' => 'Pimpinan Desa'][$role];
    $inisial = strtoupper(substr($namaUser, 0, 1));
    @endphp

    <div class="app">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-logo">SD</div>
                <div>
                    <div class="brand-nama">Sensus Desa</div>
                    <div class="brand-sub">Ekonomi &amp; Kesejahteraan</div>
                </div>
            </div>
            <div class="peran">{{ $labelPeran }}</div>

            <nav class="menu">
                @foreach ($menus as $m)
                <a href="{{ url($m['url']) }}" class="{{ request()->is(ltrim($m['url'], '/')) ? 'aktif' : '' }}">
                    <span class="titik"></span>{{ $m['nama'] }}
                </a>
                @endforeach
            </nav>

            <div class="sidebar-bawah">Mode tampilan</div>
        </aside>

        <div class="utama">
            <header class="topbar">
                <button class="burger" type="button" aria-label="Buka menu"
                    onclick="document.body.classList.toggle('menu-buka')">&#9776;</button>
                <h1>@yield('judul')</h1>
                <div class="pengguna">
                    <span class="avatar">{{ $inisial }}</span>
                    <span class="nama-user">{{ $namaUser }}</span>
                    <form method="POST" action="{{ url('/logout') }}">
                        @csrf
                        <button class="btn btn-garis" type="submit">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="isi">
                @yield('isi')
            </main>
        </div>
    </div>

    <div class="tirai" onclick="document.body.classList.remove('menu-buka')"></div>

</body>

</html>