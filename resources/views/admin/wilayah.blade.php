@extends('layouts.app')

@section('judul', 'Wilayah')

@section('isi')
@if ($errors->any())
    <div class="info"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if (session('status'))
    <div class="info">{{ session('status') }}</div>
@endif

<div class="kartu" style="margin-bottom:16px">
    <h2>Dusun</h2>
    <form method="POST" action="{{ url('/admin/wilayah/dusun') }}" class="cari">
        @csrf <input name="nama_dusun" placeholder="Nama dusun" required maxlength="100"><button class="btn btn-kecil">Tambah</button>
    </form>
    <div class="tabel-geser"><table>
        <thead><tr><th>Nama dusun</th><th>RW</th><th></th></tr></thead>
        <tbody>@forelse ($dusun as $item)
            <tr><td><form method="POST" action="{{ url('/admin/wilayah/dusun/'.$item->id) }}" class="cari">
                @csrf @method('PUT')<input name="nama_dusun" value="{{ $item->nama_dusun }}" required><button class="btn btn-garis btn-kecil">Simpan</button>
            </form></td><td>{{ $item->rw_count }}</td><td><form method="POST" action="{{ url('/admin/wilayah/dusun/'.$item->id) }}" onsubmit="return confirm('Hapus dusun ini?')">
                @csrf @method('DELETE')<button class="btn btn-bahaya btn-kecil" @disabled($item->rw_count > 0)>Hapus</button>
            </form></td></tr>
        @empty<tr><td colspan="3">Belum ada dusun.</td></tr>@endforelse</tbody>
    </table></div>
</div>

<div class="kartu" style="margin-bottom:16px">
    <h2>RW</h2>
    <form method="POST" action="{{ url('/admin/wilayah/rw') }}" class="cari">
        @csrf
        <select name="dusun_id" required><option value="">Pilih dusun</option>@foreach ($pilihanDusun as $d)<option value="{{ $d->id }}">{{ $d->nama_dusun }}</option>@endforeach</select>
        <input name="no_rw" placeholder="Nomor RW" required maxlength="10"><button class="btn btn-kecil">Tambah</button>
    </form>
    <div class="tabel-geser"><table>
        <thead><tr><th>Dusun / nomor RW</th><th>RT</th><th></th></tr></thead>
        <tbody>@forelse ($rw as $item)
            <tr><td><form method="POST" action="{{ url('/admin/wilayah/rw/'.$item->id) }}" class="cari">
                @csrf @method('PUT')
                <select name="dusun_id">@foreach ($pilihanDusun as $d)<option value="{{ $d->id }}" @selected($item->dusun_id === $d->id)>{{ $d->nama_dusun }}</option>@endforeach</select>
                <input name="no_rw" value="{{ $item->no_rw }}" required><button class="btn btn-garis btn-kecil">Simpan</button>
            </form></td><td>{{ $item->rt_count }}</td><td><form method="POST" action="{{ url('/admin/wilayah/rw/'.$item->id) }}" onsubmit="return confirm('Hapus RW ini?')">
                @csrf @method('DELETE')<button class="btn btn-bahaya btn-kecil" @disabled($item->rt_count > 0)>Hapus</button>
            </form></td></tr>
        @empty<tr><td colspan="3">Belum ada RW.</td></tr>@endforelse</tbody>
    </table></div>
</div>

<div class="kartu">
    <h2>RT</h2>
    <form method="POST" action="{{ url('/admin/wilayah/rt') }}" class="cari">
        @csrf
        <select name="rw_id" required><option value="">Pilih RW</option>@foreach ($pilihanRw as $r)<option value="{{ $r->id }}">{{ $r->dusun?->nama_dusun }} / RW {{ $r->no_rw }}</option>@endforeach</select>
        <input name="no_rt" placeholder="Nomor RT" required maxlength="10"><button class="btn btn-kecil">Tambah</button>
    </form>
    <div class="tabel-geser"><table>
        <thead><tr><th>Dusun / RW / nomor RT</th><th>Keluarga</th><th></th></tr></thead>
        <tbody>@forelse ($rt as $item)
            <tr><td><form method="POST" action="{{ url('/admin/wilayah/rt/'.$item->id) }}" class="cari">
                @csrf @method('PUT')
                <select name="rw_id">@foreach ($pilihanRw as $r)<option value="{{ $r->id }}" @selected($item->rw_id === $r->id)>{{ $r->dusun?->nama_dusun }} / RW {{ $r->no_rw }}</option>@endforeach</select>
                <input name="no_rt" value="{{ $item->no_rt }}" required><button class="btn btn-garis btn-kecil">Simpan</button>
            </form></td><td>{{ $item->keluarga_count }}</td><td><form method="POST" action="{{ url('/admin/wilayah/rt/'.$item->id) }}" onsubmit="return confirm('Hapus RT ini?')">
                @csrf @method('DELETE')<button class="btn btn-bahaya btn-kecil" @disabled($item->keluarga_count > 0)>Hapus</button>
            </form></td></tr>
        @empty<tr><td colspan="3">Belum ada RT.</td></tr>@endforelse</tbody>
    </table></div>
</div>
@endsection
