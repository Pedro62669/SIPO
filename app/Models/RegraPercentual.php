<?php

namespace App\Models;

use App\Enums\RegraPercentualTipo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegraPercentual extends Model
{
    protected $table = 'regras_percentual';

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'percentual' => 'decimal:2',
            'tipo' => RegraPercentualTipo::class,
        ];
    }

    public function orcamento(): BelongsTo
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function fonte(): BelongsTo
    {
        return $this->belongsTo(FonteRecurso::class, 'fonte_id');
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }
}
