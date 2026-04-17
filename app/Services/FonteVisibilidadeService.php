<?php

namespace App\Services;

use App\Models\FonteRecurso;
use App\Models\Unidade;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FonteVisibilidadeService
{
    public function fontesPermitidasParaUnidade(Collection $fontes, ?Unidade $unidade): Collection
    {
        return $fontes->filter(fn (FonteRecurso $fonte) => $this->fonteEhPermitidaParaUnidade($fonte, $unidade))->values();
    }

    public function fonteEhPermitidaParaUnidade(FonteRecurso $fonte, ?Unidade $unidade): bool
    {
        return match ($this->categoriaPorCodigo((string) $fonte->codigo)) {
            'educacao' => $this->unidadeEhEducacao($unidade),
            'saude' => $this->unidadeEhSaude($unidade),
            default => true,
        };
    }

    public function categoriaPorCodigo(string $codigo): string
    {
        $numero = (int) preg_replace('/\D/', '', $codigo);

        return match (true) {
            $numero >= 1540 && $numero <= 1599 => 'educacao',
            $numero >= 1600 && $numero <= 1659 => 'saude',
            default => 'livre',
        };
    }

    private function unidadeEhEducacao(?Unidade $unidade): bool
    {
        return str_contains($this->descricaoNormalizada($unidade), 'educacao');
    }

    private function unidadeEhSaude(?Unidade $unidade): bool
    {
        return str_contains($this->descricaoNormalizada($unidade), 'saude');
    }

    private function descricaoNormalizada(?Unidade $unidade): string
    {
        return Str::of($unidade?->descricao ?? '')
            ->ascii()
            ->lower()
            ->value();
    }
}
