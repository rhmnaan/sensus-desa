<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Tabel: keluarga (punya created_at dan updated_at) */
class Keluarga extends Model
{
    protected $table = 'keluarga';

    protected $fillable = ['no_kk', 'alamat', 'rt_id'];

    public function rt(): BelongsTo
    {
        return $this->belongsTo(Rt::class);
    }

    public function anggota(): HasMany
    {
        return $this->hasMany(AnggotaKeluarga::class);
    }

    public function kepalaKeluarga(): HasOne
    {
        return $this->hasOne(AnggotaKeluarga::class)->where('hubungan_keluarga', 'kepala');
    }

    public function pendataan(): HasMany
    {
        return $this->hasMany(Pendataan::class);
    }
}
