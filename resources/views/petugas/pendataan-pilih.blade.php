@extends('layouts.app')

@section('judul', 'Pilih Keluarga untuk Pendataan')

@section('isi')
@if ($errors->any())
    <div class="info">{{ $errors->first() }}</div>
@endif

<div class="judul-halaman">
    <div>
        <h2>Pilih keluarga</h2>
        <div class="ket-kecil">
            @if ($periode)
                Periode berjalan: {{ $periode->nama }} ({{ $periode->tahun }})
            @else
                Belum ada periode sensus yang sedang berjalan. Form pendataan belum dapat dibuka.
            @endif
        </div>
    </div>
    <a class="btn btn-garis" href="{{ url('/petugas/keluarga') }}">Daftar keluarga</a>
</div>

<div class="kartu">
    <form method="GET" action="{{ url('/petugas/pendataan') }}" class="cari">
        <input type="search" name="cari" value="{{ $cari }}"
            placeholder="Cari No. KK, NIK, atau nama anggota">
        <button type="submit" class="btn btn-kecil">Cari</button>
        @if ($cari !== '')
            <a class="btn btn-garis btn-kecil" href="{{ url('/petugas/pendataan') }}">Reset</a>
        @endif
    </form>

    <div class="ket-kecil" style="margin-bottom:12px">
        Pilih keluarga untuk membuka atau melanjutkan pendataan periode berjalan.
    </div>
    <div class="tabel-geser">
        <table>
            <thead>
                <tr>
                    <th>Kepala keluarga / No. KK</th>
                    <th>RT / RW</th>
                    <th>Anggota</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($keluarga as $item)
                    @php($sudahDidata = $item->sudah_didata > 0)
                    <tr>
                        <td>
                            <b>{{ $item->kepalaKeluarga?->nama ?? 'Belum ada kepala keluarga' }}</b><br>
                            <span class="ket-kecil">KK {{ $item->no_kk }}</span>
                        </td>
                        <td>RT {{ $item->rt?->no_rt ?? '-' }} / RW {{ $item->rt?->rw?->no_rw ?? '-' }}</td>
                        <td>{{ $item->anggota_count }} orang</td>
                        <td>
                            <span class="badge {{ $sudahDidata ? 'b-ok' : 'b-warn' }}">
                                {{ $periode ? ($sudahDidata ? 'Sudah didata' : 'Belum didata') : 'Periode tidak aktif' }}
                            </span>
                        </td>
                        <td style="text-align:right">
                            @if ($periode)
                                <a class="btn btn-kecil {{ $sudahDidata ? 'btn-garis' : '' }}"
                                    href="{{ url('/petugas/pendataan?keluarga_id='.$item->id) }}">
                                    {{ $sudahDidata ? 'Lanjutkan' : 'Mulai pendataan' }}
                                </a>
                            @else
                                <button class="btn btn-kecil" type="button" disabled>Belum tersedia</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="ket-kecil">Tidak ada keluarga yang cocok dengan pencarian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $keluarga->links() }}</div>
</div>
@endsection
