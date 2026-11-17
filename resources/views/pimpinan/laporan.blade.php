@extends('layouts.app')

@section('judul', 'Laporan')

@section('isi')
<style>
@media print {
    .sidebar, .topbar, .no-cetak { display: none !important; }
    .utama, .isi { margin: 0 !important; padding: 0 !important; }
    .kartu { box-shadow: none !important; border: 0 !important; padding: 0 !important; }
    body { background: #fff !important; }
    table { font-size: 11px; }
}
</style>

<div class="judul-halaman no-cetak">
    <div>
        <h2 style="margin:0">Laporan Rekap Wilayah</h2>
        <p class="ket-kecil">Rekap keluarga, pendataan, dan hasil layak bantuan per Dusun, RW, atau RT.</p>
    </div>
    <form method="GET" action="{{ url('/pimpinan/laporan') }}" class="form-grid" style="align-items:end">
        <div class="field">
            <label for="periode_id">Periode</label>
            <select id="periode_id" name="periode_id">
                @foreach ($daftarPeriode as $pilihan)
                <option value="{{ $pilihan->id }}" @selected($periode?->id === $pilihan->id)>
                    {{ $pilihan->nama }} ({{ $pilihan->tahun }})
                </option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="tingkat">Tingkat rekap</label>
            <select id="tingkat" name="tingkat">
                <option value="dusun" @selected($tingkat === 'dusun')>Dusun</option>
                <option value="rw" @selected($tingkat === 'rw')>RW</option>
                <option value="rt" @selected($tingkat === 'rt')>RT</option>
            </select>
        </div>
        <div class="field">
            <label for="dusun_id">Dusun</label>
            <select id="dusun_id" name="dusun_id">
                <option value="">Semua dusun</option>
                @foreach ($dusun as $item)
                <option value="{{ $item->id }}" @selected((string) $dusunId === (string) $item->id)>{{ $item->nama_dusun }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-utama" type="submit">Tampilkan</button>
    </form>
</div>

<div class="aksi-bar no-cetak" style="margin-bottom:16px">
    @if ($periode)
    <a class="btn btn-utama" href="{{ url('/pimpinan/laporan/ekspor?'.http_build_query(request()->query())) }}">Unduh CSV untuk Excel</a>
    @endif
    <button class="btn btn-garis" type="button" onclick="window.print()">Cetak / Simpan PDF</button>
</div>

<section class="kartu">
    <h2>Laporan Sensus Desa{{ $periode ? ' · '.$periode->nama.' ('.$periode->tahun.')' : '' }}</h2>
    @if (! $periode)
        <p class="ket-kecil">Belum ada periode sensus untuk dibuatkan laporan.</p>
    @else
        <div class="tabel-bungkus">
            <table>
                <thead>
                    <tr>
                        <th>Dusun</th>
                        @if ($tingkat !== 'dusun') <th>RW</th> @endif
                        @if ($tingkat === 'rt') <th>RT</th> @endif
                        <th>Total keluarga</th>
                        <th>Sudah didata</th>
                        <th>Layak bantuan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rekap as $baris)
                    <tr>
                        <td>{{ $baris->nama_dusun }}</td>
                        @if ($tingkat !== 'dusun') <td>{{ $baris->no_rw ?? '—' }}</td> @endif
                        @if ($tingkat === 'rt') <td>{{ $baris->no_rt ?? '—' }}</td> @endif
                        <td>{{ number_format($baris->total_keluarga) }}</td>
                        <td>{{ number_format($baris->sudah_didata) }}</td>
                        <td>{{ number_format($baris->layak_bantuan) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="{{ $tingkat === 'rt' ? 6 : ($tingkat === 'rw' ? 5 : 4) }}" class="ket-kecil">Belum ada data untuk laporan ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <p class="ket-kecil">CSV dapat dibuka di Excel. Untuk PDF, pilih “Simpan sebagai PDF” pada dialog cetak browser.</p>
    @endif
</section>
@endsection
