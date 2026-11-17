@extends('layouts.app')

@section('judul', 'Statistik')

@section('isi')
<div class="judul-halaman">
    <div>
        <h2 style="margin:0">Statistik Pendataan</h2>
        <p class="ket-kecil">Distribusi data yang sudah tersimpan pada periode terpilih.</p>
    </div>
    <form method="GET" action="{{ url('/pimpinan/statistik') }}" class="form-grid" style="align-items:end">
        <div class="field">
            <label for="periode_id">Periode sensus</label>
            <select id="periode_id" name="periode_id" onchange="this.form.submit()">
                @foreach ($daftarPeriode as $pilihan)
                <option value="{{ $pilihan->id }}" @selected($periode?->id === $pilihan->id)>
                    {{ $pilihan->nama }} ({{ $pilihan->tahun }})
                </option>
                @endforeach
            </select>
        </div>
        <noscript><button class="btn btn-utama" type="submit">Tampilkan</button></noscript>
    </form>
</div>

@if (! $periode)
    <div class="info">Belum ada periode sensus untuk ditampilkan.</div>
@else
    <div class="grid grid-3">
        <div class="kartu stat">
            <div class="label">Rata-rata penghasilan bulanan</div>
            <div class="angka" style="font-size:22px">Rp {{ number_format((float) ($statistik['ekonomi']->rata_penghasilan ?? 0), 0, ',', '.') }}</div>
            <div class="ket">dari {{ number_format((int) ($statistik['ekonomi']->jumlah ?? 0)) }} data ekonomi</div>
        </div>
        <div class="kartu stat">
            <div class="label">Rata-rata tabungan</div>
            <div class="angka" style="font-size:22px">Rp {{ number_format((float) ($statistik['ekonomi']->rata_tabungan ?? 0), 0, ',', '.') }}</div>
            <div class="ket">berdasarkan data yang tersimpan</div>
        </div>
        <div class="kartu stat">
            <div class="label">Rata-rata utang</div>
            <div class="angka" style="font-size:22px">Rp {{ number_format((float) ($statistik['ekonomi']->rata_utang ?? 0), 0, ',', '.') }}</div>
            <div class="ket">berdasarkan data yang tersimpan</div>
        </div>
    </div>

    <div class="grid grid-2">
        @include('pimpinan.bagian-statistik', ['judul' => 'Pekerjaan utama', 'data' => $statistik['pekerjaan']])
        @include('pimpinan.bagian-statistik', ['judul' => 'Pendidikan terakhir', 'data' => $statistik['pendidikan']])
        @foreach ($statistik['rumah'] as $bagian)
            @include('pimpinan.bagian-statistik', ['judul' => $bagian['label'], 'data' => $bagian['distribusi']])
        @endforeach
        @include('pimpinan.bagian-statistik', ['judul' => 'Aset keluarga (jumlah unit)', 'data' => $statistik['aset']])
        @include('pimpinan.bagian-statistik', ['judul' => 'Bantuan sosial tercatat', 'data' => $statistik['bantuan']])
    </div>
@endif
@endsection
