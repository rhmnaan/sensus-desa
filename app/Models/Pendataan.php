<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** Tabel: pendataan (tidak punya created_at dan updated_at) */
class Pendataan extends Model
{
    protected $table = 'pendataan';

    public $timestamps = false;

    protected $fillable = ['keluarga_id', 'periode_id', 'petugas_id', 'tgl_pendataan'];

    public function periode(): BelongsTo
    {
        return $this->belongsTo(PeriodeSensus::class, 'periode_id');
    }

    public function dataEkonomi(): HasOne
    {
        return $this->hasOne(DataEkonomi::class);
    }

    public function kondisiRumah(): HasOne
    {
        return $this->hasOne(KondisiRumah::class);
    }

    public function asetKeluarga(): HasMany
    {
        return $this->hasMany(AsetKeluarga::class);
    }

    public function bantuanSosial(): HasMany
    {
        return $this->hasMany(BantuanSosial::class);
    }
}
