@extends('layouts.app')

@section('judul', 'Form Pendataan')

@section('isi')
@php
$opsi = function (array $daftar, $dipilih = null) {
$h = '<option value="">Pilih...</option>';
foreach ($daftar as $v => $l) {
    $selected = (string) $v === (string) $dipilih ? ' selected' : '';
    $h .= '<option value="'.e($v).'"'.$selected.'>'.e($l).'</option>';
}
return $h;
};

// Langkah 1: kolom uang (tabel data_ekonomi)
$uang = [
'penghasilan_bulanan' => 'Penghasilan bulanan (Rp)',
'pengeluaran_makan' => 'Pengeluaran makan (Rp)',
'pengeluaran_pendidikan' => 'Pengeluaran pendidikan (Rp)',
'pengeluaran_listrik_air' => 'Pengeluaran listrik dan air (Rp)',
'pengeluaran_kesehatan' => 'Pengeluaran kesehatan (Rp)',
'pengeluaran_lainnya' => 'Pengeluaran lainnya (Rp)',
'jumlah_tabungan' => 'Jumlah tabungan (Rp)',
'jumlah_utang' => 'Jumlah utang (Rp)',
];
// Langkah 2: pilihan kondisi rumah (tabel kondisi_rumah) [label, pilihan]
$rumah = [
'status_kepemilikan' => ['Status kepemilikan', ['milik_sendiri' => 'Milik sendiri', 'sewa' => 'Sewa', 'menumpang' =>
'Menumpang', 'dinas' => 'Dinas']],
'jenis_lantai' => ['Jenis lantai', ['tanah' => 'Tanah', 'semen' => 'Semen', 'keramik' => 'Keramik', 'lainnya' =>
'Lainnya']],
'jenis_dinding' => ['Jenis dinding', ['bambu' => 'Bambu', 'kayu' => 'Kayu', 'tembok' => 'Tembok', 'lainnya' =>
'Lainnya']],
'jenis_atap' => ['Jenis atap', ['rumbia' => 'Rumbia', 'seng' => 'Seng', 'genteng' => 'Genteng', 'beton' => 'Beton',
'lainnya' => 'Lainnya']],
'sumber_air' => ['Sumber air', ['sumur' => 'Sumur', 'mata_air' => 'Mata air', 'pdam' => 'PDAM', 'sungai' => 'Sungai',
'lainnya' => 'Lainnya']],
'sumber_penerangan' => ['Sumber penerangan', ['listrik_pln' => 'Listrik PLN', 'listrik_non_pln' => 'Listrik non PLN',
'non_listrik' => 'Non listrik']],
'bahan_bakar_masak' => ['Bahan bakar memasak',['kayu' => 'Kayu', 'minyak_tanah' => 'Minyak tanah', 'lpg' => 'LPG',
'listrik' => 'Listrik', 'lainnya' => 'Lainnya']],
'fasilitas_mck' => ['Fasilitas MCK', ['sendiri' => 'Sendiri', 'bersama' => 'Bersama', 'umum' => 'Umum', 'tidak_ada' =>
'Tidak ada']],
];

$asetForm = old('aset_present')
    ? old('aset', [])
    : $asetTersimpan->map(fn ($item) => [
        'jenis_aset_id' => $item->jenis_aset_id,
        'jumlah' => $item->jumlah,
        'keterangan' => $item->keterangan,
    ])->all();
$bantuanForm = old('bantuan_present')
    ? old('bantuan', [])
    : $bantuanTersimpan->map(fn ($item) => [
        'jenis_bantuan_id' => $item->jenis_bantuan_id,
        'tahun_terima' => $item->tahun_terima,
        'keterangan' => $item->keterangan,
    ])->all();
$asetIndexBerikut = $asetForm ? max(array_keys($asetForm)) + 1 : 0;
$bantuanIndexBerikut = $bantuanForm ? max(array_keys($bantuanForm)) + 1 : 0;
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

<div class="kartu" style="margin-bottom:16px">
    <b>{{ $keluarga->kepalaKeluarga?->nama ?? 'Belum ada kepala keluarga' }}</b>
    <span class="ket-kecil">· KK {{ $keluarga->no_kk }} · RT {{ $keluarga->rt?->no_rt }} / RW {{ $keluarga->rt?->rw?->no_rw }}</span><br>
    <span class="badge b-ok">Periode {{ $periode->tahun }} (berjalan)</span>
    <span class="ket-kecil">{{ $pendataan ? 'Data periode ini sudah ada dan dapat diperbarui.' : 'Data disimpan sebagai pendataan periode ini.' }} Skor kesejahteraan belum dihitung otomatis.</span>
</div>

<div class="langkah">
    <button type="button" id="t1" class="aktif" onclick="ke(1)">1. Ekonomi</button>
    <button type="button" id="t2" onclick="ke(2)">2. Kondisi rumah</button>
    <button type="button" id="t3" onclick="ke(3)">3. Aset</button>
    <button type="button" id="t4" onclick="ke(4)">4. Bantuan sosial</button>
</div>

<form method="POST" action="{{ url('/petugas/pendataan') }}">
    @csrf
    <input type="hidden" name="keluarga_id" value="{{ $keluarga->id }}">

    {{-- LANGKAH 1: EKONOMI --}}
    <div class="kartu panel tampil" id="p1">
        <h2>Data ekonomi</h2>
        <div class="form-grid">
            @foreach ($uang as $nama => $label)
            <div class="field">
                <label>{{ $label }}</label>
                <input type="number" min="0" step="0.01" value="{{ old($nama, data_get($ekonomi, $nama, 0)) }}" name="{{ $nama }}"
                    class="{{ str_starts_with($nama, 'pengeluaran_') ? 'keluar' : '' }}">
            </div>
            @endforeach
            <div class="field"><label>Jenis usaha</label><select name="jenis_usaha_id">{!! $opsi($jenisUsaha->all(), old('jenis_usaha_id', data_get($ekonomi, 'jenis_usaha_id'))) !!}</select>
            </div>
            <div class="field"><label>Omzet bulanan (Rp)</label><input type="number" min="0" step="0.01"
                    value="{{ old('omzet_bulanan', data_get($ekonomi, 'omzet_bulanan', 0)) }}"
                    name="omzet_bulanan"></div>
            <div class="field"><label>Jumlah tenaga kerja</label><input type="number" min="0"
                    value="{{ old('jumlah_tenaga_kerja', data_get($ekonomi, 'jumlah_tenaga_kerja', 0)) }}"
                    name="jumlah_tenaga_kerja"></div>
        </div>
        <div class="kartu-sub" style="margin:16px 0 0">Total pengeluaran per bulan: <b id="total-keluar">Rp 0</b></div>
    </div>

    {{-- LANGKAH 2: KONDISI RUMAH --}}
    <div class="kartu panel" id="p2">
        <h2>Kondisi rumah</h2>
        <div class="form-grid">
            @foreach ($rumah as $nama => $r)
            <div class="field"><label>{{ $r[0] }}</label><select name="{{ $nama }}">{!! $opsi($r[1], old($nama, data_get($kondisiRumahTersimpan, $nama))) !!}</select></div>
            @endforeach
            <div class="field"><label>Luas lantai (m²)</label><input type="number" min="0" step="0.01"
                    name="luas_lantai" value="{{ old('luas_lantai', data_get($kondisiRumahTersimpan, 'luas_lantai')) }}"></div>
        </div>
    </div>

    {{-- LANGKAH 3: ASET --}}
    <div class="kartu panel" id="p3">
        <h2>Aset keluarga</h2>
        <div id="daftar-aset"></div>
        <input type="hidden" name="aset_present" value="1">
        @foreach ($asetForm as $index => $item)
        <div class="kartu-sub">
            <div class="kepala"><span>Aset</span>
                <button type="button" class="btn btn-bahaya btn-kecil" onclick="hapus(this)">Hapus</button>
            </div>
            <div class="form-grid">
                <div class="field"><label>Jenis aset</label>
                    <select name="aset[{{ $index }}][jenis_aset_id]">
                        <option value="">Pilih...</option>
                        @foreach ($jenisAset as $kategori => $daftarAset)
                        <optgroup label="{{ ucfirst($kategori) }}">
                            @foreach ($daftarAset as $jenis)
                            <option value="{{ $jenis->id }}" @selected($item['jenis_aset_id'] == $jenis->id)>
                                {{ $jenis->nama_aset }} ({{ $jenis->satuan }})
                            </option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Jumlah</label><input type="number" min="0" step="0.01"
                    name="aset[{{ $index }}][jumlah]" value="{{ $item['jumlah'] }}"></div>
                <div class="field"><label>Keterangan</label><input name="aset[{{ $index }}][keterangan]"
                    value="{{ $item['keterangan'] }}"></div>
            </div>
        </div>
        @endforeach
        <button type="button" class="btn btn-garis" onclick="tambah('tpl-aset', 'daftar-aset')">+ Tambah aset</button>
    </div>

    {{-- LANGKAH 4: BANTUAN SOSIAL --}}
    <div class="kartu panel" id="p4">
        <h2>Bantuan sosial yang pernah diterima</h2>
        <div id="daftar-bantuan"></div>
        <input type="hidden" name="bantuan_present" value="1">
        @foreach ($bantuanForm as $index => $item)
        <div class="kartu-sub">
            <div class="kepala"><span>Bantuan</span>
                <button type="button" class="btn btn-bahaya btn-kecil" onclick="hapus(this)">Hapus</button>
            </div>
            <div class="form-grid">
                <div class="field"><label>Jenis bantuan</label>
                    <select name="bantuan[{{ $index }}][jenis_bantuan_id]">
                        <option value="">Pilih...</option>
                        @foreach ($jenisBantuan as $id => $namaBantuan)
                        <option value="{{ $id }}" @selected($item['jenis_bantuan_id'] == $id)>
                            {{ $namaBantuan }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Tahun terima</label><input type="number" min="2000" max="2100"
                    name="bantuan[{{ $index }}][tahun_terima]" value="{{ $item['tahun_terima'] }}"></div>
                <div class="field"><label>Keterangan</label><input name="bantuan[{{ $index }}][keterangan]"
                    value="{{ $item['keterangan'] }}"></div>
            </div>
        </div>
        @endforeach
        <button type="button" class="btn btn-garis" onclick="tambah('tpl-bantuan', 'daftar-bantuan')">+ Tambah
            bantuan</button>
    </div>

    <div class="aksi-bar">
        <button type="button" class="btn btn-garis" id="sebelumnya" onclick="ke(langkah - 1)"
            style="visibility:hidden">Sebelumnya</button>
        <button type="button" class="btn" id="lanjut" onclick="ke(langkah + 1)">Lanjut</button>
        <button type="submit" class="btn" id="simpan" style="display:none">Simpan pendataan</button>
    </div>
</form>

{{-- Cetakan satu baris aset dan bantuan. __i__ diganti nomor urut. --}}
<template id="tpl-aset">
    <div class="kartu-sub">
        <div class="kepala"><span>Aset</span>
            <button type="button" class="btn btn-bahaya btn-kecil" onclick="hapus(this)">Hapus</button>
        </div>
        <div class="form-grid">
            <div class="field"><label>Jenis aset</label>
                <select name="aset[__i__][jenis_aset_id]">
                    <option value="">Pilih...</option>
                    @foreach ($jenisAset as $kategori => $daftarAset)
                    <optgroup label="{{ ucfirst($kategori) }}">
                        @foreach ($daftarAset as $jenis)
                            <option value="{{ $jenis->id }}">{{ $jenis->nama_aset }} ({{ $jenis->satuan }})</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="field"><label>Jumlah</label><input type="number" min="0" step="0.01" value="1"
                    name="aset[__i__][jumlah]"></div>
            <div class="field"><label>Keterangan</label><input name="aset[__i__][keterangan]"></div>
        </div>
    </div>
</template>

<template id="tpl-bantuan">
    <div class="kartu-sub">
        <div class="kepala"><span>Bantuan</span>
            <button type="button" class="btn btn-bahaya btn-kecil" onclick="hapus(this)">Hapus</button>
        </div>
        <div class="form-grid">
            <div class="field"><label>Jenis bantuan</label>
                <select name="bantuan[__i__][jenis_bantuan_id]">{!! $opsi($jenisBantuan->all()) !!}</select>
            </div>
            <div class="field"><label>Tahun terima</label><input type="number" min="2000" max="2100" value="{{ now()->year }}"
                    name="bantuan[__i__][tahun_terima]"></div>
            <div class="field"><label>Keterangan</label><input name="bantuan[__i__][keterangan]"></div>
        </div>
    </div>
</template>

<script>
var langkah = 1;
var urutan = {
    'tpl-aset': {{ $asetIndexBerikut }},
    'tpl-bantuan': {{ $bantuanIndexBerikut }}
};

// Pindah langkah
function ke(n) {
    if (n < 1 || n > 4) return;
    langkah = n;
    for (var i = 1; i <= 4; i++) {
        document.getElementById('p' + i).classList.toggle('tampil', i === n);
        document.getElementById('t' + i).classList.toggle('aktif', i === n);
    }
    document.getElementById('sebelumnya').style.visibility = n === 1 ? 'hidden' : 'visible';
    document.getElementById('lanjut').style.display = n === 4 ? 'none' : 'inline-block';
    document.getElementById('simpan').style.display = n === 4 ? 'inline-block' : 'none';
    window.scrollTo(0, 0);
}

// Tambah dan hapus baris aset atau bantuan
function tambah(idCetakan, idDaftar) {
    urutan[idCetakan] = urutan[idCetakan] || 0;
    var d = document.createElement('div');
    d.innerHTML = document.getElementById(idCetakan).innerHTML.replace(/__i__/g, urutan[idCetakan]++);
    document.getElementById(idDaftar).appendChild(d.firstElementChild);
}

function hapus(tombol) {
    tombol.closest('.kartu-sub').remove();
}

// Total pengeluaran otomatis
function hitung() {
    var total = 0;
    document.querySelectorAll('.keluar').forEach(function(e) {
        total += parseFloat(e.value) || 0;
    });
    document.getElementById('total-keluar').textContent = 'Rp ' + total.toLocaleString('id-ID');
}
document.querySelectorAll('.keluar').forEach(function(e) {
    e.addEventListener('input', hitung);
});
hitung();

</script>
@endsection