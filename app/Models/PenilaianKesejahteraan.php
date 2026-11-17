<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenilaianKesejahteraan extends Model
{
    protected $table = 'penilaian_kesejahteraan';

    public $timestamps = false;

    protected $fillable = ['pendataan_id', 'skor', 'catatan', 'dihitung_pada'];

    protected function casts(): array
    {
        return [
            'skor' => 'decimal:2',
            'dihitung_pada' => 'datetime',
        ];
    }

    public function pendataan(): BelongsTo
    {
        return $this->belongsTo(Pendataan::class);
    }
}
