<?php

namespace App\Models;

use App\Enums\LoaAcaoStatus;
use App\Enums\TipoAcao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class LoaAcao extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'loa_acoes';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tipo_acao' => TipoAcao::class,
            'status' => LoaAcaoStatus::class,
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

    public function acaoOriginal(): BelongsTo
    {
        return $this->belongsTo(Acao::class, 'acao_original_id');
    }

    public function preenchimentos(): HasMany
    {
        return $this->hasMany(LoaPreenchimento::class, 'loa_acao_id');
    }
}
