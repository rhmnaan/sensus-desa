@extends('layouts.app')

@section('judul', 'Dashboard Petugas')

@section('isi')
@if (! $periode)
<div class="info">Belum ada periode sensus yang sedang berjalan.</div>
@endif

<div class="kartu status-koneksi" style="margin-bottom:16px">
    <span class="lampu {{ $periode ? 'hidup' : 'mati' }}"></span>
    <b>{{ $periode ? 'Periode sensus berjalan' : 'Tidak ada periode aktif' }}</b>
    <span class="ket-kecil">{{ $periode ? $periode->nama.' · '.$periode->tahun : 'Data pendataan belum dihitung untuk periode aktif' }}</span>
    <a class="btn btn-garis btn-kecil" style="margin-left:auto" href="{{ url('/petugas/sinkron') }}">Lihat sinkron</a>
</div>

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
        <h2>Progres pendataan keluarga</h2>
        <div class="baris-bar">
            <div class="judul"><span>{{ $sudah }} dari {{ $total }} keluarga</span><span>{{ $persen }}%</span></div>
            <div class="bar"><span style="width: {{ $persen }}%"></span></div>
        </div>
        <a class="btn" href="{{ url('/petugas/keluarga/tambah') }}">Tambah keluarga</a>
        <a class="btn btn-garis" href="{{ url('/petugas/keluarga') }}">Daftar keluarga</a>
    </div>

    <div class="kartu">
        <h2>Keluarga yang belum didata</h2>
        <table>
            <tbody>
                @forelse ($belumList as $k)
                <tr>
                    <td>
                        <b>{{ $k->kepalaKeluarga?->nama ?? 'Belum ada kepala keluarga' }}</b><br>
                        <span class="ket-kecil">KK {{ $k->no_kk }} · RT {{ $k->rt?->no_rt ?? '-' }} / RW {{ $k->rt?->rw?->no_rw ?? '-' }}</span>
                    </td>
                    <td style="text-align:right"><a class="btn btn-kecil"
                            href="{{ url('/petugas/pendataan?keluarga_id='.$k->id) }}">Mulai</a></td>
                </tr>
                @empty
                <tr>
                    <td class="ket-kecil">Tidak ada keluarga yang menunggu pendataan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection