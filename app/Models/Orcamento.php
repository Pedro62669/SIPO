<?php

namespace App\Models;

use App\Enums\OrcamentoStatus;
use App\Enums\OrcamentoTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Orcamento extends Model implements AuditableContract
{
    use Auditable;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ano' => 'integer',
            'tipo' => OrcamentoTipo::class,
            'status' => OrcamentoStatus::class,
            'periodo_ppa_inicio' => 'integer',
            'periodo_ppa_fim' => 'integer',
            'prazo_preenchimento' => 'date',
            'is_historico' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receitas(): HasMany
    {
        return $this->hasMany(Receita::class);
    }

    public function despesasImportadas(): HasMany
    {
        return $this->hasMany(DespesaImportada::class);
    }

    public function loaPreenchimentos(): HasMany
    {
        return $this->hasMany(LoaPreenchimento::class);
    }

    public function orcamentoPrazos(): HasMany
    {
        return $this->hasMany(OrcamentoPrazo::class);
    }

    public function cortes(): HasMany
    {
        return $this->hasMany(Corte::class);
    }

    public function regrasFonte(): HasMany
    {
        return $this->hasMany(RegraFonte::class);
    }

    public function fonteUnidadeRestricoes(): HasMany
    {
        return $this->hasMany(FonteUnidadeRestricao::class);
    }

    public function parametrizacoesSecretaria(): HasMany
    {
        return $this->hasMany(ParametrizacaoSecretaria::class);
    }

    public function regrasPercentual(): HasMany
    {
        return $this->hasMany(RegraPercentual::class);
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
