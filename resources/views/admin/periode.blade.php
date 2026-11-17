@extends('layouts.app')

@section('judul', 'Periode Sensus')

@section('isi')
@if ($errors->any())
    <div class="info"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if (session('status'))
    <div class="info">{{ session('status') }}</div>
@endif

<div class="kartu" style="margin-bottom:16px">
    <h2>Tambah periode</h2>
    <form method="POST" action="{{ url('/admin/periode') }}">
        @csrf
        <div class="form-grid">
            <div class="field"><label>Nama</label><input name="nama" required maxlength="100"></div>
            <div class="field"><label>Tahun</label><input type="number" name="tahun" min="2000" max="2100" required></div>
            <div class="field"><label>Status</label><select name="status"><option value="draft">Draft</option><option value="berjalan">Berjalan</option><option value="selesai">Selesai</option></select></div>
            <div class="field"><label>Tanggal mulai</label><input type="date" name="tgl_mulai" required></div>
            <div class="field"><label>Tanggal selesai</label><input type="date" name="tgl_selesai" required></div>
        </div>
        <button class="btn" style="margin-top:14px">Tambah periode</button>
    </form>
</div>

<div class="kartu">
    <h2>Daftar periode</h2>
    <div class="tabel-geser"><table>
        <thead><tr><th>Periode</th><th>Tanggal</th><th>Status dan perubahan</th></tr></thead>
        <tbody>@forelse ($periode as $item)
            <tr><td colspan="3">
                <form method="POST" action="{{ url('/admin/periode/'.$item->id) }}">
                    @csrf @method('PUT')
                    <div class="form-grid">
                        <div class="field"><label>Nama</label><input name="nama" value="{{ $item->nama }}" required></div>
                        <div class="field"><label>Tahun</label><input type="number" name="tahun" value="{{ $item->tahun }}" min="2000" max="2100" required></div>
                        <div class="field"><label>Status</label><select name="status">
                            @foreach (['draft' => 'Draft', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'] as $value => $label)
                                <option value="{{ $value }}" @selected($item->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select></div>
                        <div class="field"><label>Tanggal mulai</label><input type="date" name="tgl_mulai" value="{{ $item->tgl_mulai }}" required></div>
                        <div class="field"><label>Tanggal selesai</label><input type="date" name="tgl_selesai" value="{{ $item->tgl_selesai }}" required></div>
                    </div>
                    <button class="btn btn-garis btn-kecil" style="margin-top:10px">Simpan perubahan</button>
                </form>
            </td></tr>
        @empty<tr><td colspan="3">Belum ada periode sensus.</td></tr>@endforelse</tbody>
    </table></div>
    <div style="margin-top:16px">{{ $periode->links() }}</div>
</div>
@endsection
