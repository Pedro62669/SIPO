<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidade extends Model
{
    protected $guarded = [];

    public function subunidades(): HasMany
    {
        return $this->hasMany(Subunidade::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function despesasImportadas(): HasMany
    {
        return $this->hasMany(DespesaImportada::class);
    }

    public function parametrizacoesSecretaria(): HasMany
    {
        return $this->hasMany(ParametrizacaoSecretaria::class);
    }

    public function loaAcoes(): HasMany
    {
        return $this->hasMany(LoaAcao::class);
    }

    public function enviosOrcamento(): HasMany
    {
        return $this->hasMany(EnvioOrcamento::class);
    }
}
