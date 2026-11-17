<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Halaman login. Kalau sudah login, langsung ke dashboard sesuai peran.
    public function form()
    {
        if (Auth::check()) {
            return $this->keDashboard(Auth::user()->canonicalRole());
        }

        return view('auth.login');
    }

    // Proses login: cocokkan username dan password dengan tabel users.
    public function masuk(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $berhasil = Auth::attempt([
            'username'  => strtolower(trim($data['username'])),
            'password'  => $data['password'],
            'is_active' => 1,
        ]);

        if (! $berhasil) {
            return back()
                ->withErrors(['username' => 'Username atau password salah, atau akun tidak aktif.'])
                ->onlyInput('username');
        }

        $request->session()->regenerate();

        return $this->keDashboard(Auth::user()->canonicalRole());
    }

    public function keluar(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    private function keDashboard(?string $role)
    {
        $tujuan = [
            'admin'    => '/admin/dashboard',
            'petugas'  => '/petugas/dashboard',
            'pimpinan' => '/pimpinan/dashboard',
        ];

        if (! isset($tujuan[$role])) {
            Auth::logout();

            return redirect('/login')
                ->withErrors(['username' => 'Peran akun belum dikenali. Hubungi administrator.']);
        }

        return redirect($tujuan[$role]);
    }
}
