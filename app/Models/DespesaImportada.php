<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class DespesaImportada extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'despesas_importadas';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor_inicial' => 'integer',
            'empenhado' => 'integer',
            'liquidado' => 'integer',
            'credito_suplementar' => 'integer',
            'credito_especial' => 'integer',
            'reducao_creditos' => 'integer',
            'dotacao_atualizada' => 'integer',
            'pago' => 'integer',
            'saldo_a_liquidar' => 'integer',
            'saldo_a_pagar' => 'integer',
            'saldo_dotacao' => 'integer',
            'saldo_disponivel' => 'integer',
        ];
    }

    public function orcamento(): BelongsTo
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function natureza(): BelongsTo
    {
        return $this->belongsTo(Natureza::class);
    }

    public function fonte(): BelongsTo
    {
        return $this->belongsTo(FonteRecurso::class, 'fonte_id');
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function subunidade(): BelongsTo
    {
        return $this->belongsTo(Subunidade::class);
    }

    public function funcao(): BelongsTo
    {
        return $this->belongsTo(Funcao::class);
    }

    public function subfuncao(): BelongsTo
    {
        return $this->belongsTo(Subfuncao::class);
    }

    public function programa(): BelongsTo
    {
        return $this->belongsTo(Programa::class);
    }

    public function acao(): BelongsTo
    {
        return $this->belongsTo(Acao::class);
    }
}
