<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Programa extends Model
{
    protected $guarded = [];

    public function acoes(): HasMany
    {
        return $this->hasMany(Acao::class);
    }
}
