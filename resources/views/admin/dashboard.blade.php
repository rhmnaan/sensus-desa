@extends('layouts.app')

@section('judul', 'Dashboard Admin')

@section('isi')
@php
$warnaStatus = [
    'Berhasil' => 'b-ok',
    'Gagal' => 'b-bahaya',
    'Menunggu' => 'b-warn',
    'Berjalan' => 'b-warn',
    'Mencoba ulang' => 'b-warn',
    'Mulai' => 'b-warn',
    'Tidak diketahui' => 'b-warn',
];
@endphp

@if (! $periode)
    <div class="info">Belum ada periode sensus yang sedang berjalan.</div>
@endif

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
        @forelse ($dusun as $d)
        <div class="baris-bar">
            <div class="judul"><span>{{ $d->nama_dusun }}</span><span>{{ $d->persen }}%</span></div>
            <div class="bar"><span style="width: {{ $d->persen }}%"></span></div>
        </div>
        @empty
        <p class="ket-kecil">Belum ada data dusun.</p>
        @endforelse
    </div>

    <div class="kartu">
        <h2>Sinkronisasi terakhir</h2>
        @if (! $sumberSinkronTersedia)
            <p class="ket-kecil">Database riwayat replikasi belum tersedia.</p>
        @endif
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Publikasi</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sinkron as $s)
                <tr>
                    <td>{{ $s['waktu'] }}<br><small style="color:var(--abu)">{{ $s['jenis'] }}</small></td>
                    <td>{{ $s['publikasi'] }}</td>
                    <td><span class="badge {{ $warnaStatus[$s['status']] }}">{{ $s['status'] }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="ket-kecil">Belum ada riwayat sinkronisasi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
