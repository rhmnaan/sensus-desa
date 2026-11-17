<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Tabel: users (dibuat lewat sensus_desa.sql, bukan migration Laravel).
 * Kolom: id, nama, username, password, role, no_hp, is_active, created_at, updated_at
 */
class User extends Authenticatable
{
    protected $table = 'users';

    protected $fillable = ['nama', 'username', 'password', 'role', 'no_hp', 'is_active'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function canonicalRole(): ?string
    {
        return match (strtolower(trim((string) $this->role))) {
            'admin', 'administrator' => 'admin',
            'petugas', 'petugas lapangan', 'petugas_lapangan' => 'petugas',
            'pimpinan', 'pimpinan desa', 'pimpinan_desa' => 'pimpinan',
            default => null,
        };
    }
}