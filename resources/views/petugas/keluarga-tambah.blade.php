@extends('layouts.app')

@section('judul', $keluarga ? 'Ubah Keluarga' : 'Tambah Keluarga')

@section('isi')
@php
// Pembuat isi dropdown dari daftar [nilai => tulisan]
$opsi = function (array $daftar, $dipilih = null) {
$h = '<option value="">Pilih...</option>';
foreach ($daftar as $v => $l) {
    $selected = (string) $v === (string) $dipilih ? ' selected' : '';
    $h .= '<option value="'.e($v).'"'.$selected.'>'.e($l).'</option>';
}
return $h;
};

$hubungan = ['kepala' => 'Kepala keluarga', 'istri' => 'Istri', 'anak' => 'Anak', 'orang_tua' => 'Orang tua', 'famili'
=> 'Famili lain', 'lainnya' => 'Lainnya'];
$kawin = ['belum_kawin' => 'Belum kawin', 'kawin' => 'Kawin', 'cerai_hidup' => 'Cerai hidup', 'cerai_mati' => 'Cerai
mati'];
$pendidikan = ['tidak_sekolah' => 'Tidak sekolah', 'sd' => 'SD', 'smp' => 'SMP', 'sma' => 'SMA', 'diploma' => 'Diploma',
'sarjana' => 'Sarjana', 'pascasarjana' => 'Pascasarjana'];
$sekolah = ['tidak_sekolah' => 'Tidak sekolah', 'masih_sekolah' => 'Masih sekolah', 'tidak_sekolah_lagi' => 'Tidak
sekolah lagi'];
@endphp

@if ($errors->any())
    <div class="info">
        <b>Periksa kembali data yang diisi:</b>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $keluarga ? url('/petugas/keluarga/'.$keluarga->id) : url('/petugas/keluarga/tambah') }}">
    @csrf
    @if ($keluarga)
        @method('PUT')
    @endif

    <div class="kartu" style="margin-bottom:16px">
        <h2>Data keluarga</h2>
        <div class="form-grid">
            <div class="field">
                <label>No. KK</label>
                <input name="no_kk" maxlength="16" inputmode="numeric" placeholder="16 digit"
                    value="{{ old('no_kk', $keluarga?->no_kk) }}" required>
            </div>
            <div class="field">
                <label>RT</label>
                <select name="rt_id" required>{!! $opsi($opsiRt->all(), old('rt_id', $keluarga?->rt_id)) !!}</select>
            </div>
            <div class="field lebar">
                <label>Alamat</label>
                <textarea name="alamat" rows="2" placeholder="Jalan, nomor rumah, patokan">{{ old('alamat', $keluarga?->alamat) }}</textarea>
            </div>
        </div>
    </div>

    <div class="kartu">
        <h2>Anggota keluarga</h2>
        <div id="daftar-anggota"></div>
        <button type="button" class="btn btn-garis" onclick="tambahAnggota()">+ Tambah anggota</button>
    </div>

    <div class="aksi-bar">
        <a class="btn btn-garis" href="{{ url('/petugas/keluarga') }}">Batal</a>
        <button type="submit" class="btn">{{ $keluarga ? 'Simpan perubahan' : 'Simpan keluarga' }}</button>
    </div>
</form>

{{-- Cetakan satu anggota. Tombol "Tambah anggota" menggandakan isi ini. __i__ diganti nomor urut. --}}
<template id="tpl-anggota">
    <div class="kartu-sub">
        <input type="hidden" name="anggota[__i__][id]">
        <div class="kepala">
            <span>Anggota <span class="no"></span></span>
            <button type="button" class="btn btn-bahaya btn-kecil" onclick="hapusAnggota(this)">Hapus</button>
        </div>
        <div class="form-grid">
            <div class="field"><label>NIK</label><input name="anggota[__i__][nik]" maxlength="16" inputmode="numeric"
                    placeholder="16 digit" required></div>
            <div class="field"><label>Nama</label><input name="anggota[__i__][nama]" required></div>
            <div class="field"><label>Jenis kelamin</label>
                <select name="anggota[__i__][jenis_kelamin]" required>{!! $opsi(['L' => 'Laki-laki', 'P' => 'Perempuan'])
                    !!}</select>
            </div>
            <div class="field"><label>Tanggal lahir</label><input type="date" name="anggota[__i__][tanggal_lahir]" required>
            </div>
            <div class="field"><label>Hubungan keluarga</label>
                <select name="anggota[__i__][hubungan_keluarga]" required>{!! $opsi($hubungan) !!}</select>
            </div>
            <div class="field"><label>Status perkawinan</label>
                <select name="anggota[__i__][status_perkawinan]" required>{!! $opsi($kawin) !!}</select>
            </div>
            <div class="field"><label>Pendidikan terakhir</label>
                <select name="anggota[__i__][pendidikan_terakhir]" required>{!! $opsi($pendidikan) !!}</select>
            </div>
            <div class="field"><label>Status sekolah</label>
                <select name="anggota[__i__][status_sekolah]" required>{!! $opsi($sekolah) !!}</select>
            </div>
            <div class="field"><label>Pekerjaan utama</label>
                <select name="anggota[__i__][pekerjaan_utama_id]">{!! $opsi($pekerjaan->all()) !!}</select>
            </div>
            <div class="field"><label>Pekerjaan sampingan</label>
                <select name="anggota[__i__][pekerjaan_sampingan_id]">{!! $opsi($pekerjaan->all()) !!}</select>
            </div>
            <div class="field centang">
                <label><input type="checkbox" name="anggota[__i__][disabilitas]" value="1"> Disabilitas</label>
                <label><input type="checkbox" name="anggota[__i__][penyakit_kronis]" value="1"> Penyakit kronis</label>
            </div>
        </div>
    </div>
</template>

<script>
var cetakan = document.getElementById('tpl-anggota');
var daftar = document.getElementById('daftar-anggota');
var urutan = 0;

function tambahAnggota(data) {
    var indeks = urutan++;
    var d = document.createElement('div');
    d.innerHTML = cetakan.innerHTML.replace(/__i__/g, indeks);
    daftar.appendChild(d.firstElementChild);
    Object.keys(data || {}).forEach(function(kunci) {
        var kolom = Array.from(daftar.querySelectorAll('input, select')).find(function(field) {
            return field.name === 'anggota[' + indeks + '][' + kunci + ']';
        });
        if (!kolom) return;
        if (kolom.type === 'checkbox') {
            kolom.checked = Boolean(Number(data[kunci]));
        } else {
            kolom.value = data[kunci];
        }
    });
    beriNomor();
}

function hapusAnggota(tombol) {
    tombol.closest('.kartu-sub').remove();
    beriNomor();
}

function beriNomor() {
    daftar.querySelectorAll('.no').forEach(function(e, i) {
        e.textContent = i + 1;
    });
}

var anggotaLama = @json($anggotaForm);
if (anggotaLama.length) {
    anggotaLama.forEach(tambahAnggota);
} else {
    tambahAnggota();
    daftar.querySelector('select[name$="[hubungan_keluarga]"]').value = 'kepala';
}
</script>
@endsection