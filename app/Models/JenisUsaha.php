<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisUsaha extends Model
{
    protected $table = 'jenis_usaha';

    public $timestamps = false;

    protected $fillable = ['nama_jenis'];

    public function dataEkonomi(): HasMany
    {
        return $this->hasMany(DataEkonomi::class);
    }
}
