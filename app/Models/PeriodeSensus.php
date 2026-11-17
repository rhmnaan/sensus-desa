<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Tabel: periode_sensus (tidak punya created_at dan updated_at) */
class PeriodeSensus extends Model
{
    protected $table = 'periode_sensus';

    public $timestamps = false;

    protected $fillable = ['nama', 'tahun', 'tgl_mulai', 'tgl_selesai', 'status'];

    /** Periode yang sedang berjalan (null kalau belum ada). */
    public static function berjalan(): ?self
    {
        return static::where('status', 'berjalan')->orderByDesc('tahun')->first();
    }
}
