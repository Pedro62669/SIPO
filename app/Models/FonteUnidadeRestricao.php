<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FonteUnidadeRestricao extends Model
{
    protected $table = 'fonte_unidade_restricoes';

    protected $guarded = [];

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
}
