<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisBantuan extends Model
{
    protected $table = 'jenis_bantuan';

    public $timestamps = false;

    protected $fillable = ['kode', 'nama_bantuan'];

    public function bantuanSosial(): HasMany
    {
        return $this->hasMany(BantuanSosial::class);
    }
}
