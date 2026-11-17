@extends('layouts.app')

@section('judul', 'Detail Keluarga Layak Bantuan')

@section('isi')
<div class="judul-halaman">
    <div>
        <h2 style="margin:0">Detail hasil penilaian</h2>
        <p class="ket-kecil">Informasi ini hanya dapat dilihat dan tidak dapat diubah dari akun Pimpinan.</p>
    </div>
    <a class="btn btn-garis" href="{{ url('/pimpinan/layak-bantuan?periode_id='.$keluarga->periode_id) }}">Kembali ke daftar</a>
</div>

<div class="grid grid-2">
    <section class="kartu">
        <h2>Identitas keluarga</h2>
        <p><b>Kepala keluarga:</b> {{ $keluarga->kepala_keluarga ?? 'Belum tersedia' }}</p>
        <p><b>No. KK:</b> {{ $keluarga->no_kk }}</p>
        <p><b>Alamat:</b> {{ $keluarga->alamat }}</p>
        <p><b>Wilayah:</b> {{ $keluarga->nama_dusun }} · RW {{ $keluarga->no_rw }} / RT {{ $keluarga->no_rt }}</p>
        <p><b>Tanggal pendataan:</b> {{ $keluarga->tgl_pendataan }}</p>
    </section>
    <section class="kartu">
        <h2>Hasil penilaian</h2>
        <p><b>Kategori:</b> {{ $keluarga->nama_kategori }}</p>
        <p><b>Skor tersimpan:</b> {{ number_format((float) $keluarga->skor, 2, ',', '.') }}</p>
        <p><b>Catatan:</b> {{ $keluarga->catatan ?: 'Tidak ada catatan.' }}</p>
        <p class="ket-kecil">Skor tidak dihitung ulang oleh halaman ini.</p>
    </section>
</div>
@endsection
