<?php

namespace App\Imports;

use App\Models\FonteRecurso;
use App\Models\Receita;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ReceitaImport implements SkipsEmptyRows, ToModel, WithHeadingRow
{
    public function __construct(
        private int $orcamentoId
    ) {}

    public function model(array $row): ?Receita
    {
        $natureza = $row['natureza_da_receita'] ?? $row['natureza'] ?? null;
        $descricao = $row['descricao'] ?? $row['descrição'] ?? null;
        $fonte = $row['fonte_de_recurso'] ?? $row['fonte'] ?? null;
        $valor = $row['valor'] ?? 0;

        if (! $natureza) {
            return null;
        }

        $fonteCodigo = trim((string) ($fonte ?? ''));
        if ($fonteCodigo !== '') {
            FonteRecurso::firstOrCreate(
                ['codigo' => $fonteCodigo],
                ['descricao' => 'Fonte '.$fonteCodigo]
            );
        }

        $valorCentavos = $this->parseValorParaCentavos($valor);

        return new Receita([
            'orcamento_id' => $this->orcamentoId,
            'natureza_receita' => trim((string) $natureza),
            'descricao' => trim((string) ($descricao ?? '')),
            'fonte_recurso' => $fonteCodigo,
            'valor' => $valorCentavos,
            'eh_deducao' => false,
        ]);
    }

    /**
     * Converte valor em reais para centavos (mesma base de valor_liberado na parametrização).
     * Strings no formato brasileiro (ex.: 28.000.000,00) são interpretadas pela última vírgula como decimal.
     * Valores numéricos do Excel (float/int) são tratados como reais inteiros ou com decimais nativos.
     */
    private function parseValorParaCentavos(mixed $valor): int
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        if (is_object($valor)) {
            $valor = method_exists($valor, '__toString') ? (string) $valor : '';
        }

        if (is_string($valor)) {
            return $this->parseBrStringParaCentavos($valor);
        }

        if (is_int($valor)) {
            return $valor * 100;
        }

        if (is_float($valor)) {
            if (is_nan($valor) || is_infinite($valor)) {
                return 0;
            }

            return (int) round($valor * 100);
        }

        if (is_numeric($valor)) {
            return (int) round(((float) $valor) * 100);
        }

        return 0;
    }

    /**
     * Interpreta texto no padrão brasileiro (milhar com ponto, decimais com vírgula).
     * Remove caracteres estranhos (espaços finos, símbolos) que o Excel costuma enviar.
     */
    private function parseBrStringParaCentavos(string $raw): int
    {
        $s = trim($raw);
        if ($s === '' || $s === '-') {
            return 0;
        }

        $neg = str_starts_with($s, '-');
        if ($neg) {
            $s = ltrim(substr($s, 1));
        }

        $s = preg_replace('/^R\$\s*/iu', '', $s);
        // Vírgula unicode (algumas planilhas)
        $s = str_replace(["\xEF\xBC\x8C", "\xe2\x80\x9a"], ',', $s);
        // Remove espaços comuns, NBSP e narrow NBSP (planilhas)
        $s = str_replace(["\xc2\xa0", "\xe2\x80\xaf", ' '], '', $s);
        // Normaliza separadores unicode de milhar
        $s = str_replace(["\xe2\x80\x89", "\xe2\x80\x87"], '', $s);

        if ($s === '') {
            return 0;
        }

        // Última vírgula = separador decimal BR (ex.: 28.000.000,00)
        if (str_contains($s, ',')) {
            $lastComma = strrpos($s, ',');
            $parteInteira = substr($s, 0, $lastComma);
            $parteFrac = substr($s, $lastComma + 1);
            $parteInteira = str_replace('.', '', $parteInteira);
            $parteInteira = preg_replace('/[^\d\-]/', '', $parteInteira);
            $parteFrac = preg_replace('/\D/', '', $parteFrac);
            $parteFrac = substr(str_pad($parteFrac, 2, '0', STR_PAD_RIGHT), 0, 2);

            $reais = (int) $parteInteira;
            $cent = (int) $parteFrac;

            $total = $reais * 100 + $cent;

            return $neg ? -$total : $total;
        }

        // Sem vírgula: apenas milhares com ponto ou número “cru” (ex.: 28000000)
        $s = str_replace('.', '', $s);
        $s = preg_replace('/[^\d\-]/', '', $s);

        $reais = (int) $s;
        $total = $reais * 100;

        return $neg ? -$total : $total;
    }
}
