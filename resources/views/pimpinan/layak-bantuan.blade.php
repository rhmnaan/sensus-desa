@extends('layouts.app')

@section('judul', 'Layak Bantuan')

@section('isi')
<div class="judul-halaman">
    <div>
        <h2 style="margin:0">Keluarga Layak Bantuan</h2>
        <p class="ket-kecil">Daftar baca-saja berdasarkan skor tersimpan dan kategori bertanda layak bantuan.</p>
    </div>
    <form method="GET" action="{{ url('/pimpinan/layak-bantuan') }}" class="form-grid" style="align-items:end">
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
            <label for="dusun_id">Dusun</label>
            <select id="dusun_id" name="dusun_id">
                <option value="">Semua dusun</option>
                @foreach ($dusun as $item)
                <option value="{{ $item->id }}" @selected((string) request('dusun_id') === (string) $item->id)>{{ $item->nama_dusun }}</option>
                @endforeach
            </select>
        </div>
        <div class="field">
            <label for="q">Cari keluarga</label>
            <input id="q" name="q" value="{{ request('q') }}" placeholder="No. KK, kepala keluarga, alamat">
        </div>
        <button class="btn btn-utama" type="submit">Terapkan</button>
    </form>
</div>

@if (! $periode)
    <div class="info">Belum ada periode sensus untuk ditampilkan.</div>
@else
    <div class="kartu">
        <div class="ket-kecil" style="margin-bottom:12px">
            Daftar ini hanya menampilkan keluarga yang sudah memiliki hasil penilaian dan skor tersebut masuk ke kategori bertanda layak bantuan.
        </div>
        <div class="tabel-bungkus">
            <table>
                <thead>
                    <tr>
                        <th>Kepala keluarga / No. KK</th>
                        <th>Dusun · RW/RT</th>
                        <th>Kategori</th>
                        <th>Skor</th>
                        <th>Tanggal pendataan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($keluarga as $item)
                    <tr>
                        <td>{{ $item->kepala_keluarga ?? 'Nama kepala keluarga belum tersedia' }}<br><small>{{ $item->no_kk }}</small></td>
                        <td>{{ $item->nama_dusun }} · {{ $item->no_rw }}/{{ $item->no_rt }}</td>
                        <td>{{ $item->nama_kategori }}</td>
                        <td>{{ number_format((float) $item->skor, 2, ',', '.') }}</td>
                        <td>{{ $item->tgl_pendataan }}</td>
                        <td><a class="btn btn-garis btn-kecil" href="{{ url('/pimpinan/layak-bantuan/'.$item->pendataan_id) }}">Detail</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="ket-kecil">Belum ada keluarga layak bantuan yang tercatat untuk periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (method_exists($keluarga, 'links'))
            <div style="margin-top:16px">{{ $keluarga->links() }}</div>
        @endif
    </div>
@endif
@endsection
