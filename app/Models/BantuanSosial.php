<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BantuanSosial extends Model
{
    protected $table = 'bantuan_sosial';

    public $timestamps = false;

    protected $fillable = ['pendataan_id', 'jenis_bantuan_id', 'tahun_terima', 'keterangan'];

    public function pendataan(): BelongsTo
    {
        return $this->belongsTo(Pendataan::class);
    }

    public function jenisBantuan(): BelongsTo
    {
        return $this->belongsTo(JenisBantuan::class);
    }
}
