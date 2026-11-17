<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsetKeluarga extends Model
{
    protected $table = 'aset_keluarga';

    public $timestamps = false;

    protected $fillable = ['pendataan_id', 'jenis_aset_id', 'jumlah', 'keterangan'];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
        ];
    }

    public function pendataan(): BelongsTo
    {
        return $this->belongsTo(Pendataan::class);
    }

    public function jenisAset(): BelongsTo
    {
        return $this->belongsTo(JenisAset::class);
    }
}
