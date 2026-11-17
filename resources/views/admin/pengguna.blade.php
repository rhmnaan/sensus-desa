@extends('layouts.app')

@section('judul', 'Pengguna')

@section('isi')
@if ($errors->any())
    <div class="info"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
@if (session('status'))
    <div class="info">{{ session('status') }}</div>
@endif

<div class="kartu" style="margin-bottom:16px">
    <h2>Tambah pengguna</h2>
    <form method="POST" action="{{ url('/admin/pengguna') }}">
        @csrf
        <div class="form-grid">
            <div class="field"><label>Nama</label><input name="nama" value="{{ old('nama') }}" required maxlength="100"></div>
            <div class="field"><label>Username</label><input name="username" value="{{ old('username') }}" required maxlength="50"></div>
            <div class="field"><label>Peran</label>
                <select name="role" required>
                    <option value="admin">Admin</option><option value="petugas">Petugas</option><option value="pimpinan">Pimpinan</option>
                </select>
            </div>
            <div class="field"><label>No. HP</label><input name="no_hp" value="{{ old('no_hp') }}" maxlength="20"></div>
            <div class="field"><label>Password awal</label><input type="password" name="password" required minlength="8" autocomplete="new-password"></div>
            <div class="field"><label>Ulangi password</label><input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password"></div>
        </div>
        <button class="btn" type="submit" style="margin-top:14px">Tambah pengguna</button>
    </form>
</div>

<div class="kartu">
    <h2>Daftar pengguna</h2>
    <div class="tabel-geser">
        <table>
            <thead><tr><th>Nama / username</th><th>Peran</th><th>No. HP</th><th>Status</th><th>Pengaturan</th></tr></thead>
            <tbody>
                @forelse ($pengguna as $user)
                    <tr>
                        <td>
                            <form method="POST" action="{{ url('/admin/pengguna/'.$user->id) }}" class="form-grid dua">
                                @csrf @method('PUT')
                                <div class="field"><label>Nama</label><input name="nama" value="{{ $user->nama }}" required></div>
                                <div class="field"><label>Username</label><input name="username" value="{{ $user->username }}" required></div>
                                <div class="field"><label>Peran</label><select name="role">
                                    @foreach (['admin' => 'Admin', 'petugas' => 'Petugas', 'pimpinan' => 'Pimpinan'] as $value => $label)
                                        <option value="{{ $value }}" @selected($user->canonicalRole() === $value)>{{ $label }}</option>
                                    @endforeach
                                </select></div>
                                <div class="field"><label>No. HP</label><input name="no_hp" value="{{ $user->no_hp }}"></div>
                                <button class="btn btn-kecil" type="submit">Simpan profil</button>
                            </form>
                        </td>
                        <td>{{ ucfirst($user->canonicalRole() ?? $user->role) }}</td>
                        <td>{{ $user->no_hp ?: '-' }}</td>
                        <td><span class="badge {{ $user->is_active ? 'b-ok' : 'b-bahaya' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                        <td>
                            <form method="POST" action="{{ url('/admin/pengguna/'.$user->id.'/aktif') }}" style="margin-bottom:10px">
                                @csrf @method('PATCH')
                                <button class="btn btn-garis btn-kecil" type="submit" @disabled(auth()->id() === $user->id)>
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ url('/admin/pengguna/'.$user->id.'/password') }}">
                                @csrf @method('PUT')
                                <label>Password baru</label>
                                <input type="password" name="password" required minlength="8" autocomplete="new-password">
                                <label>Ulangi password</label>
                                <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                                <button class="btn btn-garis btn-kecil" type="submit" style="margin-top:6px">Reset password</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ket-kecil">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $pengguna->links() }}</div>
</div>
@endsection
