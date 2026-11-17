@extends('layouts.app')

@section('judul', 'Status Sinkron')

@section('isi')
@php
$warna = [
    'Berhasil' => 'b-ok',
    'Gagal' => 'b-bahaya',
    'Menunggu' => 'b-warn',
    'Berjalan' => 'b-warn',
    'Mencoba ulang' => 'b-warn',
    'Mulai' => 'b-warn',
    'Tidak diketahui' => 'b-warn',
];
@endphp

@if ($errors->any())
    <div class="info">{{ $errors->first() }}</div>
@endif
@if (session('status'))
    <div class="info">{{ session('status') }}</div>
@endif

<div class="judul-halaman">
    <div class="ket-kecil">Jalankan pull subscription Merge pada SQL Server Agent laptop ini.</div>
    <form method="POST" action="{{ url('/petugas/sinkron') }}">
        @csrf
        <button class="btn" type="submit">Sinkronkan sekarang</button>
    </form>
</div>

<div class="kartu status-koneksi" style="margin-bottom:16px">
    <span class="lampu {{ $sumberTersedia ? '' : 'mati' }}"></span>
    <b>{{ $sumberTersedia ? 'Riwayat replikasi tersedia' : 'Riwayat replikasi belum tersedia' }}</b>
    <span class="ket-kecil">Sumber: riwayat SQL Server Distribution dan Merge Replication. Job sinkron berjalan terpisah dari aplikasi.</span>
</div>

<div class="grid grid-3" style="margin-bottom:16px">
    <div class="kartu stat">
        <div class="label">Menunggu dikirim</div>
        <div class="angka">{{ $ringkasan['menunggu'] ?? '—' }}</div>
        <div class="ket">Jumlah antrean perubahan tidak disediakan oleh riwayat job.</div>
    </div>
    <div class="kartu stat">
        <div class="label">Sinkronisasi berhasil</div>
        <div class="angka">{{ $ringkasan['berhasil'] ?? '—' }}</div>
        <div class="ket">Total riwayat job Merge berstatus berhasil.</div>
    </div>
    <div class="kartu stat">
        <div class="label">Konflik tercatat</div>
        <div class="angka">{{ $ringkasan['konflik'] ?? '—' }}</div>
        <div class="ket">Jumlah konflik jika tabel metadata tersedia pada database ini.</div>
    </div>
</div>

<div class="kartu">
    <h2>Riwayat</h2>
    <div class="tabel-geser">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Jenis</th>
                    <th>Publikasi</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $r)
                <tr>
                    <td>{{ $r['waktu'] }}</td>
                    <td>{{ $r['jenis'] }}</td>
                    <td>{{ $r['publikasi'] }}</td>
                    <td><span class="badge {{ $warna[$r['status']] }}">{{ $r['status'] }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="ket-kecil">
                        {{ $sumberTersedia ? 'Belum ada riwayat sinkronisasi.' : 'Database riwayat replikasi tidak dapat diakses dari server ini.' }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="kartu" style="margin-top:16px">
    <h2>Arti status konflik</h2>
    <p>Konflik terjadi ketika data yang sama diubah di lebih dari satu lokasi sebelum replikasi Merge berjalan. SQL Server mencatat konflik sesuai aturan resolver publikasi; periksa catatan konflik di SQL Server sebelum menentukan data yang dipertahankan.</p>
    <p class="ket-kecil">Angka antrean atau konflik ditampilkan sebagai tanda pisah jika tidak tersedia dari koneksi atau metadata SQL Server. Status riwayat job tidak digunakan sebagai perkiraan jumlah baris yang menunggu dikirim.</p>
</div>
@endsection
