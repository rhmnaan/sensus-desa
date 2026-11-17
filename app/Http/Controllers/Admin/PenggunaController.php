<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PenggunaController extends Controller
{
    public function index(): View
    {
        return view('admin.pengguna', [
            'pengguna' => User::query()->orderBy('nama')->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')],
            'role' => ['required', Rule::in(['admin', 'petugas', 'pimpinan'])],
            'no_hp' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::query()->create($data + ['is_active' => true]);

        return back()->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('users', 'username')->ignore($user)],
            'role' => ['required', Rule::in(['admin', 'petugas', 'pimpinan'])],
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        if ($user->is_active && $user->canonicalRole() === 'admin' && $data['role'] !== 'admin') {
            if (! $this->adaAdminAktifLain($user)) {
                return back()->withErrors(['pengguna' => 'Tidak dapat mengubah satu-satunya admin aktif.']);
            }
        }

        $user->update($data);

        return back()->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleAktif(User $user): RedirectResponse
    {
        if ($user->is((auth()->user()))) {
            return back()->withErrors(['pengguna' => 'Akun yang sedang digunakan tidak dapat dinonaktifkan.']);
        }

        if ($user->is_active && $user->canonicalRole() === 'admin') {
            if (! $this->adaAdminAktifLain($user)) {
                return back()->withErrors(['pengguna' => 'Tidak dapat menonaktifkan satu-satunya admin aktif.']);
            }
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('status', $user->is_active ? 'Pengguna berhasil diaktifkan.' : 'Pengguna berhasil dinonaktifkan.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => $data['password']]);

        return back()->with('status', "Password pengguna {$user->username} berhasil direset.");
    }

    private function adaAdminAktifLain(User $dikecualikan): bool
    {
        return User::query()
            ->where('is_active', true)
            ->where('id', '!=', $dikecualikan->id)
            ->get()
            ->contains(fn (User $user): bool => $user->canonicalRole() === 'admin');
    }
}
