<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Funcao extends Model
{
    protected $table = 'funcoes';

    protected $guarded = [];

    public function subfuncoes(): HasMany
    {
        return $this->hasMany(Subfuncao::class);
    }
}
