@extends('layouts.app')

@section('judul', 'Penilaian Kesejahteraan')

@section('isi')
<div class="info">
    Perhitungan skor dan hitung ulang belum diaktifkan. Rumus, bobot indikator, serta ambang kategori harus disetujui terlebih dahulu agar hasil penilaian tidak menyesatkan.
</div>

<div class="grid grid-2">
    <div class="kartu stat">
        <div class="label">Hasil penilaian tersimpan</div>
        <div class="angka">{{ $jumlahHasil }}</div>
        <div class="ket">Data yang sudah tercatat pada tabel penilaian.</div>
    </div>
    <div class="kartu">
        <h2>Kategori dan ambang yang terdaftar</h2>
        <div class="tabel-geser">
            <table>
                <thead><tr><th>Kategori</th><th>Rentang skor</th><th>Layak bantuan</th></tr></thead>
                <tbody>
                    @forelse ($kategori as $item)
                        <tr>
                            <td>{{ $item->nama_kategori }}</td>
                            <td>{{ $item->skor_min }}–{{ $item->skor_max }}</td>
                            <td>{{ $item->layak_bantuan ? 'Ya' : 'Tidak' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="ket-kecil">Kategori belum diatur pada Data Master.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="kartu" style="margin-top:16px">
    <h2>Langkah berikutnya</h2>
    <p>Atur rentang kategori melalui Data Master setelah ambang disepakati. Fitur hitung dan hitung ulang akan dibuat setelah rumus skor final ditentukan.</p>
    <a class="btn btn-garis" href="{{ url('/admin/master') }}">Buka Data Master</a>
</div>
@endsection
