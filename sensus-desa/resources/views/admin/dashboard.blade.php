@extends('layouts.app')

@section('judul', 'Dashboard Admin')

@section('isi')
@php
// ===== DATA CONTOH =====
// Nanti bagian ini diganti dengan data dari database (lewat Controller).
$statistik = [
['label' => 'Total Keluarga', 'nilai' => 320, 'ket' => 'terdaftar di sistem'],
['label' => 'Sudah Didata', 'nilai' => 214, 'ket' => 'periode 2026'],
['label' => 'Belum Didata', 'nilai' => 106, 'ket' => 'masih perlu didatangi'],
['label' => 'Konflik Menunggu', 'nilai' => 3, 'ket' => 'perlu ditinjau admin'],
];

$dusun = [
['nama' => 'Dusun A', 'persen' => 78],
['nama' => 'Dusun B', 'persen' => 64],
['nama' => 'Dusun C', 'persen' => 52],
['nama' => 'Dusun D', 'persen' => 31],
];

$sinkron = [
['waktu' => '28 Okt 2026 10:15', 'perangkat' => 'SERVER-LAPANGAN', 'jenis' => 'Merge', 'status' => 'Berhasil'],
['waktu' => '28 Okt 2026 09:40', 'perangkat' => 'SERVER-LAPANGAN', 'jenis' => 'Merge', 'status' => 'Konflik'],
['waktu' => '27 Okt 2026 16:05', 'perangkat' => 'SERVER-LAPANGAN', 'jenis' => 'Snapshot', 'status' => 'Berhasil'],
['waktu' => '27 Okt 2026 08:30', 'perangkat' => 'SERVER-LAPANGAN', 'jenis' => 'Merge', 'status' => 'Gagal'],
];

$warnaStatus = ['Berhasil' => 'b-ok', 'Konflik' => 'b-warn', 'Gagal' => 'b-bahaya'];
@endphp

<div class="info">Data pada halaman ini masih contoh. Nanti diganti data asli dari database.</div>

<div class="grid grid-4">
    @foreach ($statistik as $s)
    <div class="kartu stat">
        <div class="label">{{ $s['label'] }}</div>
        <div class="angka">{{ $s['nilai'] }}</div>
        <div class="ket">{{ $s['ket'] }}</div>
    </div>
    @endforeach
</div>

<div class="grid grid-2">
    <div class="kartu">
        <h2>Progres pendataan per dusun</h2>
        @foreach ($dusun as $d)
        <div class="baris-bar">
            <div class="judul"><span>{{ $d['nama'] }}</span><span>{{ $d['persen'] }}%</span></div>
            <div class="bar"><span style="width: {{ $d['persen'] }}%"></span></div>
        </div>
        @endforeach
    </div>

    <div class="kartu">
        <h2>Sinkronisasi terakhir</h2>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Jenis</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sinkron as $s)
                <tr>
                    <td>{{ $s['waktu'] }}<br><small style="color:var(--abu)">{{ $s['perangkat'] }}</small></td>
                    <td>{{ $s['jenis'] }}</td>
                    <td><span class="badge {{ $warnaStatus[$s['status']] }}">{{ $s['status'] }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection