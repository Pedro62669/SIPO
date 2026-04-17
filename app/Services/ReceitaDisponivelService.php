<?php

namespace App\Services;

use App\Models\ParametrizacaoSecretaria;
use App\Models\Receita;
use App\Models\RegraFonte;

class ReceitaDisponivelService
{
    /**
     * Soma das receitas importadas (em centavos) alocadas ao código de fonte destino,
     * aplicando regras de substituição origem → destino do orçamento.
     * Deduções (eh_deducao) reduzem o total.
     */
    public function totalReceitaCentavosPorCodigoFonte(int $orcamentoId, string $codigoFonteDestino): int
    {
        $codigoFonteDestino = $this->normalizarCodigoFonte($codigoFonteDestino);

        $regras = RegraFonte::query()
            ->where('orcamento_id', $orcamentoId)
            ->get()
            ->mapWithKeys(fn ($r) => [$this->normalizarCodigoFonte($r->fonte_origem) => $this->normalizarCodigoFonte($r->fonte_destino)]);

        $total = 0;

        foreach (Receita::where('orcamento_id', $orcamentoId)->cursor() as $rec) {
            $origem = $this->normalizarCodigoFonte((string) $rec->fonte_recurso);
            $destino = $regras[$origem] ?? $origem;

            if ($destino !== $codigoFonteDestino) {
                continue;
            }

            $v = (int) $rec->valor;
            $total += $rec->eh_deducao ? -$v : $v;
        }

        return max(0, $total);
    }

    public function totalLiberadoCentavosPorFonteId(int $orcamentoId, int $fonteId): int
    {
        return (int) ParametrizacaoSecretaria::query()
            ->where('orcamento_id', $orcamentoId)
            ->where('fonte_id', $fonteId)
            ->sum('valor_liberado');
    }

    /**
     * Saldo em centavos: receita importada − soma dos valores já liberados para a fonte.
     */
    public function saldoCentavosDisponivel(int $orcamentoId, int $fonteId, string $codigoFonte): int
    {
        $receita = $this->totalReceitaCentavosPorCodigoFonte($orcamentoId, $codigoFonte);
        $liberado = $this->totalLiberadoCentavosPorFonteId($orcamentoId, $fonteId);

        return max(0, $receita - $liberado);
    }

    /** Soma líquida de todas as linhas da planilha de receita (centavos). */
    public function totalReceitaCentavosOrcamento(int $orcamentoId): int
    {
        $total = 0;
        foreach (Receita::where('orcamento_id', $orcamentoId)->cursor() as $rec) {
            $v = (int) $rec->valor;
            $total += $rec->eh_deducao ? -$v : $v;
        }

        return max(0, $total);
    }

    public function totalLiberadoCentavosOrcamento(int $orcamentoId): int
    {
        return (int) ParametrizacaoSecretaria::query()
            ->where('orcamento_id', $orcamentoId)
            ->sum('valor_liberado');
    }

    private function normalizarCodigoFonte(string $codigo): string
    {
        return trim($codigo);
    }
}
