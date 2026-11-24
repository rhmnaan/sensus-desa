@extends('layouts.app')

@section('judul', 'Monitor Replikasi')

@section('isi')
@php
$warnaStatus = [
    'Berhasil' => 'b-ok',
    'Berjalan' => 'b-ok',
    'Gagal' => 'b-bahaya',
    'Nonaktif' => 'b-bahaya',
    'Menunggu' => 'b-warn',
    'Siaga' => 'b-warn',
    'Mencoba ulang' => 'b-warn',
    'Mulai' => 'b-warn',
    'Tidak berjalan' => 'b-warn',
    'Tidak tersedia' => 'b-warn',
    'Tidak diketahui' => 'b-warn',
];
@endphp

<div class="judul-halaman">
    <span class="ket-kecil">Data diambil saat halaman dibuka. Perbarui untuk memeriksa kondisi terbaru.</span>
    <a class="btn btn-kecil" href="{{ url('/admin/replikasi') }}">Perbarui data</a>
</div>

@if ($monitor['koneksi'] === 'terhubung')
    <div class="info" style="background:var(--ok-bg);color:var(--ok)">
        SQL Server terhubung · Database {{ $monitor['database'] ?: 'tidak diketahui' }}
    </div>
@elseif ($monitor['koneksi'] === 'gagal')
    <div class="info">{{ $monitor['pesanKoneksi'] }}</div>
@else
    <div class="info">{{ $monitor['pesanKoneksi'] }}</div>
@endif

<div class="grid grid-4">
    <div class="kartu stat">
        <div class="label">Koneksi</div>
        <div class="angka">{{ $monitor['koneksi'] === 'terhubung' ? 'Aktif' : 'Tidak tersedia' }}</div>
        <div class="ket">Koneksi database aplikasi ke SQL Server</div>
    </div>
    <div class="kartu stat">
        <div class="label">Agen berjalan</div>
        <div class="angka">{{ $monitor['agenTersedia'] ? $monitor['jumlahBerjalan'] : '—' }}</div>
        <div class="ket">Dari agen yang dapat dibaca</div>
    </div>
    <div class="kartu stat">
        <div class="label">Sinkronisasi berhasil terakhir</div>
        <div class="angka" style="font-size:18px">{{ $monitor['sinkronTerakhir']['waktu'] ?? '—' }}</div>
        <div class="ket">{{ $monitor['sinkronTerakhir'] ? $monitor['sinkronTerakhir']['jenis'].' · '.$monitor['sinkronTerakhir']['publikasi'] : 'Belum ada riwayat berhasil' }}</div>
    </div>
    <div class="kartu stat">
        <div class="label">Konflik tercatat</div>
        <div class="angka">{{ $monitor['konflik'] ?? '—' }}</div>
        <div class="ket">Berdasarkan metadata konflik Merge yang tersedia</div>
    </div>
</div>

<div class="grid grid-2">
    <div class="kartu">
        <h2>Status agen</h2>
        @unless ($monitor['agenTersedia'])
            <p class="ket-kecil">Status agen belum dapat dibaca. Pastikan database Distribution dan izin baca SQL Server Agent tersedia.</p>
        @endunless
        <div class="tabel-geser">
            <table>
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Publikasi</th>
                        <th>Job SQL Server Agent</th>
                        <th>Status</th>
                        <th>Mulai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($monitor['agen'] as $agen)
                    <tr>
                        <td>{{ $agen['jenis'] }}</td>
                        <td>{{ $agen['publikasi'] ?: '-' }}</td>
                        <td>{{ $agen['job'] ?: '-' }}</td>
                        <td><span class="badge {{ $warnaStatus[$agen['status']] ?? 'b-warn' }}">{{ $agen['status'] }}</span></td>
                        <td>{{ $agen['mulai'] ?: '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="ket-kecil">
                            {{ $monitor['agenTersedia'] ? 'Belum ada agen replikasi yang terdaftar.' : 'Informasi agen tidak tersedia.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="kartu">
        <h2>Error terbaru</h2>
        @if (! $monitor['riwayatTersedia'])
            <p class="ket-kecil">Riwayat error belum tersedia. Pastikan database Distribution dapat dibaca.</p>
        @endif
        <div class="tabel-geser">
            <table>
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Jenis / publikasi</th>
                        <th>Status</th>
                        <th>Pesan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($monitor['kesalahan'] as $error)
                    <tr>
                        <td>{{ $error['waktu'] }}</td>
                        <td>{{ $error['jenis'] }}<br>{{ $error['publikasi'] }}</td>
                        <td><span class="badge {{ $warnaStatus[$error['status']] ?? 'b-warn' }}">{{ $error['status'] }}</span></td>
                        <td>{{ $error['pesan'] ?: 'Pesan tidak disediakan oleh riwayat replikasi.' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="ket-kecil">
                            {{ $monitor['riwayatTersedia'] ? 'Tidak ada error pada riwayat terbaru.' : 'Riwayat error tidak tersedia.' }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="kartu" style="margin-top:16px">
    <h2>Riwayat replikasi terbaru</h2>
    @unless ($monitor['riwayatTersedia'])
        <p class="ket-kecil">Belum bisa mengambil riwayat dari database Distribution.</p>
    @endunless
    <div class="tabel-geser">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Jenis</th>
                    <th>Publikasi</th>
                    <th>Status</th>
                    <th>Pesan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($monitor['riwayat'] as $baris)
                <tr>
                    <td>{{ $baris['waktu'] }}</td>
                    <td>{{ $baris['jenis'] }}</td>
                    <td>{{ $baris['publikasi'] }}</td>
                    <td><span class="badge {{ $warnaStatus[$baris['status']] ?? 'b-warn' }}">{{ $baris['status'] }}</span></td>
                    <td>{{ $baris['pesan'] ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="ket-kecil">Belum ada riwayat replikasi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
