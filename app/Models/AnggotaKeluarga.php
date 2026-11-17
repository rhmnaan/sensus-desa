<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnggotaKeluarga extends Model
{
    protected $table = 'anggota_keluarga';

    public $timestamps = false;

    protected $fillable = [
        'keluarga_id',
        'nik',
        'nama',
        'jenis_kelamin',
        'tanggal_lahir',
        'hubungan_keluarga',
        'status_perkawinan',
        'pendidikan_terakhir',
        'status_sekolah',
        'pekerjaan_utama_id',
        'pekerjaan_sampingan_id',
        'disabilitas',
        'penyakit_kronis',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'disabilitas' => 'boolean',
            'penyakit_kronis' => 'boolean',
        ];
    }

    public function keluarga(): BelongsTo
    {
        return $this->belongsTo(Keluarga::class);
    }

    public function pekerjaanUtama(): BelongsTo
    {
        return $this->belongsTo(Pekerjaan::class, 'pekerjaan_utama_id');
    }

    public function pekerjaanSampingan(): BelongsTo
    {
        return $this->belongsTo(Pekerjaan::class, 'pekerjaan_sampingan_id');
    }
}
