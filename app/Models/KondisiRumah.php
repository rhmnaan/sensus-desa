<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KondisiRumah extends Model
{
    protected $table = 'kondisi_rumah';

    public $timestamps = false;

    protected $fillable = [
        'pendataan_id',
        'status_kepemilikan',
        'jenis_lantai',
        'jenis_dinding',
        'jenis_atap',
        'luas_lantai',
        'sumber_air',
        'sumber_penerangan',
        'bahan_bakar_masak',
        'fasilitas_mck',
    ];

    protected function casts(): array
    {
        return [
            'luas_lantai' => 'decimal:2',
        ];
    }

    public function pendataan(): BelongsTo
    {
        return $this->belongsTo(Pendataan::class);
    }
}
