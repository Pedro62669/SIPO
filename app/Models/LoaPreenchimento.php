<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class LoaPreenchimento extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'loa_preenchimentos';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valor' => 'integer',
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

    public function loaAcao(): BelongsTo
    {
        return $this->belongsTo(LoaAcao::class, 'loa_acao_id');
    }

    public function natureza(): BelongsTo
    {
        return $this->belongsTo(Natureza::class);
    }

    public function fonte(): BelongsTo
    {
        return $this->belongsTo(FonteRecurso::class, 'fonte_id');
    }

    public function corte(): HasOne
    {
        return $this->hasOne(Corte::class, 'loa_preenchimento_id');
    }
}
