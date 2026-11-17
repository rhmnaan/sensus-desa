<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dusun extends Model
{
    protected $table = 'dusun';

    public $timestamps = false;

    protected $fillable = ['nama_dusun'];

    public function rw(): HasMany
    {
        return $this->hasMany(Rw::class);
    }
}
