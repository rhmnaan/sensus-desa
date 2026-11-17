<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pekerjaan extends Model
{
    protected $table = 'pekerjaan';

    public $timestamps = false;

    protected $fillable = ['nama_pekerjaan'];

    public function anggotaDenganPekerjaanUtama(): HasMany
    {
        return $this->hasMany(AnggotaKeluarga::class, 'pekerjaan_utama_id');
    }

    public function anggotaDenganPekerjaanSampingan(): HasMany
    {
        return $this->hasMany(AnggotaKeluarga::class, 'pekerjaan_sampingan_id');
    }
}
