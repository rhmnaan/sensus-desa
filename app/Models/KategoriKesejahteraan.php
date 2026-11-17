<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriKesejahteraan extends Model
{
    protected $table = 'kategori_kesejahteraan';

    public $timestamps = false;

    protected $fillable = ['nama_kategori', 'skor_min', 'skor_max', 'layak_bantuan'];

    protected function casts(): array
    {
        return [
            'skor_min' => 'decimal:2',
            'skor_max' => 'decimal:2',
            'layak_bantuan' => 'boolean',
        ];
    }
}
