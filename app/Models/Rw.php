<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rw extends Model
{
    protected $table = 'rw';

    public $timestamps = false;

    protected $fillable = ['dusun_id', 'no_rw'];

    public function dusun(): BelongsTo
    {
        return $this->belongsTo(Dusun::class);
    }

    public function rt(): HasMany
    {
        return $this->hasMany(Rt::class);
    }
}
