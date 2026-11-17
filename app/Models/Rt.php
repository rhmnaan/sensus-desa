<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rt extends Model
{
    protected $table = 'rt';

    public $timestamps = false;

    protected $fillable = ['rw_id', 'no_rt'];

    public function rw(): BelongsTo
    {
        return $this->belongsTo(Rw::class);
    }

    public function keluarga(): HasMany
    {
        return $this->hasMany(Keluarga::class);
    }
}
