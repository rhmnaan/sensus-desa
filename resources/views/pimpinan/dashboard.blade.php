@extends('layouts.app')

@section('judul', 'Dashboard Pimpinan')

@section('isi')
<div class="judul-halaman">
    <div>
        <h2 style="margin:0">Ringkasan sensus</h2>
        <p class="ket-kecil">Ringkasan berdasarkan periode sensus yang dipilih.</p>
    </div>
    <form method="GET" action="{{ url('/pimpinan/dashboard') }}" class="form-grid" style="align-items:end">
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
    <div class="info">Belum ada periode sensus. Setelah periode dibuat, ringkasan akan tampil di sini.</div>
@else
    <div class="grid grid-4">
        @foreach ($kartu as $item)
        <div class="kartu stat">
            <div class="label">{{ $item['label'] }}</div>
            <div class="angka">{{ number_format($item['nilai'], 0, ',', '.') }}</div>
            <div class="ket">{{ $item['keterangan'] }}</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-2">
        <section class="kartu">
            <h2>Kategori kesejahteraan</h2>
            @php($maksKategori = max(1, (int) $kategori->max('jumlah')))
            @forelse ($kategori as $item)
            <div class="baris-bar">
                <div class="judul">
                    <span>{{ $item->label }}{{ $item->layak_bantuan ? ' · Layak bantuan' : '' }}</span>
                    <span>{{ number_format($item->jumlah) }}</span>
                </div>
                <div class="bar"><span style="width: {{ min(100, (int) round($item->jumlah / $maksKategori * 100)) }}%"></span></div>
            </div>
            @empty
            <p class="ket-kecil">Belum ada kategori penilaian atau hasil penilaian pada periode ini.</p>
            @endforelse
            <p class="ket-kecil">Kategori dihitung dari skor yang sudah tersimpan dan rentang pada Data Master; aplikasi tidak menghitung skor baru.</p>
        </section>

        <section class="kartu">
            <h2>Progres pendataan per dusun</h2>
            @forelse ($dusun as $item)
            <div class="baris-bar">
                <div class="judul">
                    <span>{{ $item->label }}</span>
                    <span>{{ $item->terdata }} / {{ $item->total }} ({{ $item->persen }}%)</span>
                </div>
                <div class="bar"><span style="width: {{ $item->persen }}%"></span></div>
            </div>
            @empty
            <p class="ket-kecil">Belum ada data dusun.</p>
            @endforelse
        </section>
    </div>
@endif
@endsection
