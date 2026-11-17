@extends('layouts.app')

@section('judul', 'Detail Keluarga')

@section('isi')
@if (session('status'))
    <div class="info">{{ session('status') }}</div>
@endif

<div class="judul-halaman">
    <div>
        <h1>{{ $keluarga->kepalaKeluarga?->nama ?? 'Detail keluarga' }}</h1>
        <div class="ket-kecil">No. KK {{ $keluarga->no_kk }}</div>
    </div>
    <div class="aksi">
        <a class="btn btn-garis" href="{{ url('/petugas/keluarga') }}">Kembali</a>
        <a class="btn btn-garis" href="{{ url('/petugas/keluarga/'.$keluarga->id.'/ubah') }}">Ubah keluarga</a>
        <a class="btn" href="{{ url('/petugas/pendataan?keluarga_id='.$keluarga->id) }}">Pendataan periode berjalan</a>
    </div>
</div>

<div class="kartu" style="margin-bottom:16px">
    <h2>Informasi keluarga</h2>
    <div class="form-grid">
        <div><span class="ket-kecil">Alamat</span><br>{{ $keluarga->alamat ?: 'Belum diisi' }}</div>
        <div><span class="ket-kecil">Wilayah</span><br>
            Dusun {{ $keluarga->rt?->rw?->dusun?->nama_dusun ?? '-' }},
            RW {{ $keluarga->rt?->rw?->no_rw ?? '-' }},
            RT {{ $keluarga->rt?->no_rt ?? '-' }}
        </div>
    </div>
</div>

<div class="kartu" style="margin-bottom:16px">
    <h2>Anggota keluarga ({{ $keluarga->anggota->count() }})</h2>
    <div class="tabel-geser">
        <table>
            <thead>
                <tr>
                    <th>Nama / NIK</th>
                    <th>Hubungan</th>
                    <th>Jenis kelamin</th>
                    <th>Tanggal lahir</th>
                    <th>Pendidikan</th>
                    <th>Pekerjaan utama</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($keluarga->anggota as $anggota)
                    <tr>
                        <td><b>{{ $anggota->nama }}</b><br><span class="ket-kecil">{{ $anggota->nik }}</span></td>
                        <td>{{ str_replace('_', ' ', ucfirst($anggota->hubungan_keluarga)) }}</td>
                        <td>{{ $anggota->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                        <td>{{ $anggota->tanggal_lahir->format('d-m-Y') }}</td>
                        <td>{{ strtoupper($anggota->pendidikan_terakhir) }}</td>
                        <td>{{ $anggota->pekerjaanUtama?->nama_pekerjaan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ket-kecil">Belum ada anggota keluarga.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="kartu">
    <h2>Riwayat pendataan</h2>
    <div class="tabel-geser">
        <table>
            <thead><tr><th>Periode</th><th>Tanggal pendataan</th></tr></thead>
            <tbody>
                @forelse ($keluarga->pendataan->sortByDesc('tgl_pendataan') as $pendataan)
                    <tr>
                        <td>{{ $pendataan->periode?->nama ?? 'Periode tidak ditemukan' }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($pendataan->tgl_pendataan)->format('d-m-Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="ket-kecil">Belum ada riwayat pendataan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
