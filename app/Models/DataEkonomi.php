<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataEkonomi extends Model
{
    protected $table = 'data_ekonomi';

    public $timestamps = false;

    protected $fillable = [
        'pendataan_id',
        'penghasilan_bulanan',
        'pengeluaran_makan',
        'pengeluaran_pendidikan',
        'pengeluaran_listrik_air',
        'pengeluaran_kesehatan',
        'pengeluaran_lainnya',
        'jumlah_tabungan',
        'jumlah_utang',
        'jenis_usaha_id',
        'omzet_bulanan',
        'jumlah_tenaga_kerja',
    ];

    protected function casts(): array
    {
        return [
            'penghasilan_bulanan' => 'decimal:2',
            'pengeluaran_makan' => 'decimal:2',
            'pengeluaran_pendidikan' => 'decimal:2',
            'pengeluaran_listrik_air' => 'decimal:2',
            'pengeluaran_kesehatan' => 'decimal:2',
            'pengeluaran_lainnya' => 'decimal:2',
            'jumlah_tabungan' => 'decimal:2',
            'jumlah_utang' => 'decimal:2',
            'omzet_bulanan' => 'decimal:2',
        ];
    }

    public function pendataan(): BelongsTo
    {
        return $this->belongsTo(Pendataan::class);
    }

    public function jenisUsaha(): BelongsTo
    {
        return $this->belongsTo(JenisUsaha::class);
    }
}
