@extends('layouts.app')

@section('judul', 'Data Master')

@section('isi')
@if ($errors->any())
    <div class="info"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if (session('status'))
    <div class="info">{{ session('status') }}</div>
@endif

@foreach ([
    'pekerjaan' => ['Pekerjaan', 'nama_pekerjaan'],
    'usaha' => ['Jenis Usaha', 'nama_jenis'],
    'aset' => ['Jenis Aset', 'nama_aset'],
    'bantuan' => ['Jenis Bantuan', 'nama_bantuan'],
    'kategori' => ['Kategori Kesejahteraan', 'nama_kategori'],
] as $jenis => [$judul, $kolom])
    <div class="kartu" style="margin-bottom:16px">
        <h2>{{ $judul }}</h2>
        <form method="POST" action="{{ url('/admin/master/'.$jenis) }}" class="cari">
            @csrf
            @if ($jenis === 'aset')
                <input name="kategori" placeholder="Kategori aset" required maxlength="50">
            @elseif ($jenis === 'bantuan')
                <input name="kode" placeholder="Kode bantuan" required maxlength="20">
            @endif
            <input name="{{ $kolom }}" placeholder="{{ $judul }}" required maxlength="100">
            @if ($jenis === 'aset')
                <input name="satuan" placeholder="Satuan" required maxlength="30">
            @elseif ($jenis === 'kategori')
                <input type="number" name="skor_min" placeholder="Skor minimum" min="0" step="0.01" required>
                <input type="number" name="skor_max" placeholder="Skor maksimum" min="0" step="0.01" required>
                <label><input type="checkbox" name="layak_bantuan" value="1"> Layak bantuan</label>
            @endif
            <button class="btn btn-kecil">Tambah</button>
        </form>
        <div class="tabel-geser"><table>
            <thead><tr>
                @if ($jenis === 'aset')<th>Kategori</th>@endif
                @if ($jenis === 'bantuan')<th>Kode</th>@endif
                <th>{{ $judul }}</th>
                @if ($jenis === 'aset')<th>Satuan</th>@endif
                @if ($jenis === 'kategori')<th>Rentang skor</th><th>Layak bantuan</th>@endif
                <th>Aksi</th>
            </tr></thead>
            <tbody>
                @forelse ($$jenis as $item)
                    <tr><td colspan="{{ $jenis === 'aset' || $jenis === 'kategori' ? 4 : ($jenis === 'bantuan' ? 3 : 2) }}">
                        <form method="POST" action="{{ url('/admin/master/'.$jenis.'/'.$item->id) }}" class="cari">
                            @csrf @method('PUT')
                            @if ($jenis === 'aset')<input name="kategori" value="{{ $item->kategori }}" required>@endif
                            @if ($jenis === 'bantuan')<input name="kode" value="{{ $item->kode }}" required>@endif
                            <input name="{{ $kolom }}" value="{{ $item->$kolom }}" required>
                            @if ($jenis === 'aset')<input name="satuan" value="{{ $item->satuan }}" required>@endif
                            @if ($jenis === 'kategori')
                                <input type="number" name="skor_min" value="{{ $item->skor_min }}" min="0" step="0.01" required>
                                <input type="number" name="skor_max" value="{{ $item->skor_max }}" min="0" step="0.01" required>
                                <label><input type="checkbox" name="layak_bantuan" value="1" @checked($item->layak_bantuan)> Layak bantuan</label>
                            @endif
                            <button class="btn btn-garis btn-kecil">Simpan</button>
                        </form>
                    </td><td>
                        <form method="POST" action="{{ url('/admin/master/'.$jenis.'/'.$item->id) }}" onsubmit="return confirm('Hapus data master ini?')">
                            @csrf @method('DELETE')<button class="btn btn-bahaya btn-kecil">Hapus</button>
                        </form>
                    </td></tr>
                @empty
                    <tr><td colspan="5" class="ket-kecil">Belum ada {{ strtolower($judul) }}.</td></tr>
                @endforelse
            </tbody>
        </table></div>
    </div>
@endforeach
@endsection
