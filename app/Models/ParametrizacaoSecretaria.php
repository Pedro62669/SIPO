<?php

namespace App\Models;

use App\Enums\ParametrizacaoClassificacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ParametrizacaoSecretaria extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'parametrizacoes_secretaria';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'classificacao' => ParametrizacaoClassificacao::class,
            'percentual_anterior' => 'decimal:2',
            'valor_liberado' => 'integer',
        ];
    }

    public function orcamento(): BelongsTo
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function subunidade(): BelongsTo
    {
        return $this->belongsTo(Subunidade::class);
    }

    public function fonte(): BelongsTo
    {
        return $this->belongsTo(FonteRecurso::class, 'fonte_id');
    }
}
