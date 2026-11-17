@extends('layouts.app')

@section('judul', 'Daftar Keluarga')

@section('isi')
@php($warnaStatus = ['Belum' => 'b-bahaya', 'Terdata' => 'b-ok'])
@php($labelStatusMerge = $statusMerge === 'konfigurasi_ada' ? 'Merge terkonfigurasi' : 'Merge belum dikonfigurasi')
@php($warnaStatusMerge = $statusMerge === 'konfigurasi_ada' ? 'b-ok' : 'b-warn')

@if ($errors->any())
    <div class="info">{{ $errors->first() }}</div>
@endif
@if (session('status'))
    <div class="info">{{ session('status') }}</div>
@endif

<div class="judul-halaman">
    <div class="ket-kecil">
        Menampilkan {{ $keluarga->count() }} dari {{ $keluarga->total() }} hasil · {{ $jumlahKeluarga }} keluarga terdaftar
        @if ($periode)
            · Periode {{ $periode->tahun }}
        @else
            · Belum ada periode sensus berjalan
        @endif
    </div>
    <a class="btn" href="{{ url('/petugas/keluarga/tambah') }}">+ Tambah keluarga</a>
</div>

<div class="kartu">
    <div class="ket-kecil" style="margin-bottom:12px">
        Badge Merge menunjukkan ketersediaan konfigurasi job sinkronisasi; status per keluarga dilacak oleh SQL Server Merge Replication.
    </div>
    <form method="GET" action="{{ url('/petugas/keluarga') }}" class="cari">
        <input type="search" name="cari" value="{{ $filters['cari'] }}"
            placeholder="Cari No. KK, NIK, atau nama kepala keluarga">
        <select name="rt_id">
            <option value="" @selected($filters['rt_id'] === '')>Semua RT</option>
            @foreach ($wilayahRt as $rt)
                <option value="{{ $rt->id }}" @selected((string) $filters['rt_id'] === (string) $rt->id)>
                    RT {{ $rt->no_rt }} / RW {{ $rt->rw?->no_rw }}
                </option>
            @endforeach
        </select>
        <select name="status">
            <option value="semua" @selected($filters['status'] === 'semua')>Semua status</option>
            <option value="belum" @selected($filters['status'] === 'belum')>Belum didata</option>
            <option value="terdata" @selected($filters['status'] === 'terdata')>Terdata</option>
        </select>
        <button type="submit" class="btn btn-kecil">Cari</button>
        @if ($filters['cari'] !== '' || $filters['rt_id'] !== '' || $filters['status'] !== 'semua')
            <a class="btn btn-garis btn-kecil" href="{{ url('/petugas/keluarga') }}">Reset</a>
        @endif
    </form>

    <div class="tabel-geser">
        <table>
            <thead>
                <tr>
                    <th>Kepala keluarga</th>
                    <th>RT / RW</th>
                    <th>Anggota</th>
                    <th>Pendataan</th>
                    <th>Status sinkron</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($keluarga as $k)
                @php($statusPendataan = $k->sudah_didata > 0 ? 'Terdata' : 'Belum')
                <tr>
                    <td>
                        <b>{{ $k->kepalaKeluarga?->nama ?? 'Belum ada kepala keluarga' }}</b><br>
                        <span class="ket-kecil">KK {{ $k->no_kk }}</span>
                    </td>
                    <td>RT {{ $k->rt?->no_rt }} / RW {{ $k->rt?->rw?->no_rw }}</td>
                    <td>{{ $k->anggota_count }} orang</td>
                    <td><span class="badge {{ $warnaStatus[$statusPendataan] }}">{{ $statusPendataan }}</span></td>
                    <td><span class="badge {{ $warnaStatusMerge }}">{{ $labelStatusMerge }}</span></td>
                    <td style="text-align:right">
                        <a class="btn btn-garis btn-kecil" href="{{ url('/petugas/keluarga/'.$k->id) }}">Lihat</a>
                        <a class="btn btn-garis btn-kecil" href="{{ url('/petugas/keluarga/'.$k->id.'/ubah') }}">Ubah</a>
                        <a class="btn btn-kecil {{ $statusPendataan === 'Terdata' ? 'btn-garis' : '' }}"
                            href="{{ url('/petugas/pendataan?keluarga_id='.$k->id) }}">
                            {{ $statusPendataan === 'Terdata' ? 'Lanjutkan' : 'Mulai pendataan' }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="ket-kecil">Tidak ada keluarga yang cocok dengan pencarian atau filter.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">
        {{ $keluarga->links() }}
    </div>
</div>
@endsection