<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisAset extends Model
{
    protected $table = 'jenis_aset';

    public $timestamps = false;

    protected $fillable = ['kategori', 'nama_aset', 'satuan'];

    public function asetKeluarga(): HasMany
    {
        return $this->hasMany(AsetKeluarga::class);
    }
}
