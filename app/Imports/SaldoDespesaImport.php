<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class SaldoDespesaImport implements ToCollection
{
    public Collection $importedRows;

    public function __construct()
    {
        $this->importedRows = collect();
    }

    public function collection(Collection $rows): void
    {
        $headerIdx = null;
        $colMap = [];

        foreach ($rows as $idx => $row) {
            $vals = $row->map(fn ($v) => mb_strtolower(trim((string) ($v ?? ''))))->toArray();

            $hasDespesa = false;
            $hasValorIndicator = false;

            foreach ($vals as $val) {
                if (str_contains($val, 'despesa') || $val === 'nr' || $val === 'número') {
                    $hasDespesa = true;
                }
                if (
                    str_contains($val, 'dotação') || str_contains($val, 'dotacao') ||
                    str_contains($val, 'empenhado') || str_contains($val, 'inicial')
                ) {
                    $hasValorIndicator = true;
                }
            }

            if ($hasDespesa && $hasValorIndicator) {
                $headerIdx = $idx;

                foreach ($vals as $i => $val) {
                    if (! isset($colMap['numero_despesa']) && (str_contains($val, 'despesa') || $val === 'nr')) {
                        $colMap['numero_despesa'] = $i;
                        continue;
                    }

                    if (
                        ! isset($colMap['valor_inicial']) &&
                        (str_contains($val, 'dotação inicial') || str_contains($val, 'dotacao inicial') ||
                            str_contains($val, '1.dotação') || str_contains($val, '1.dotacao'))
                    ) {
                        $colMap['valor_inicial'] = $i;
                        continue;
                    }

                    if (! isset($colMap['credito_suplementar']) && str_contains($val, 'suplementar')) {
                        $colMap['credito_suplementar'] = $i;
                        continue;
                    }

                    if (! isset($colMap['credito_especial']) && str_contains($val, 'especial')) {
                        $colMap['credito_especial'] = $i;
                        continue;
                    }

                    if (! isset($colMap['reducao_creditos']) && str_contains($val, 'redução') || (! isset($colMap['reducao_creditos']) && str_contains($val, 'reducao'))) {
                        $colMap['reducao_creditos'] = $i;
                        continue;
                    }

                    if (
                        ! isset($colMap['dotacao_atualizada']) &&
                        (str_contains($val, 'dotação atualizada') || str_contains($val, 'dotacao atualizada') ||
                            str_contains($val, '9.dotação') || str_contains($val, '9.dotacao'))
                    ) {
                        $colMap['dotacao_atualizada'] = $i;
                        continue;
                    }

                    if (! isset($colMap['empenhado']) && str_contains($val, 'empenhado') && str_contains($val, 'líquido')) {
                        $colMap['empenhado'] = $i;
                        continue;
                    }
                    if (! isset($colMap['empenhado']) && str_contains($val, '10.empenhado')) {
                        $colMap['empenhado'] = $i;
                        continue;
                    }

                    if (! isset($colMap['liquidado']) && str_contains($val, 'liquidado') && str_contains($val, 'líquido')) {
                        $colMap['liquidado'] = $i;
                        continue;
                    }
                    if (! isset($colMap['liquidado']) && str_contains($val, '12.liquidado')) {
                        $colMap['liquidado'] = $i;
                        continue;
                    }

                    if (! isset($colMap['pago']) && str_contains($val, 'pago') && str_contains($val, 'financeiro')) {
                        $colMap['pago'] = $i;
                        continue;
                    }
                    if (! isset($colMap['pago']) && str_contains($val, '14.pago')) {
                        $colMap['pago'] = $i;
                        continue;
                    }

                    if (! isset($colMap['total_pago']) && str_contains($val, 'total pago') || (! isset($colMap['total_pago']) && str_contains($val, '15.total'))) {
                        $colMap['total_pago'] = $i;
                        continue;
                    }

                    if (! isset($colMap['saldo_a_liquidar']) && str_contains($val, 'saldo a liquidar') || (! isset($colMap['saldo_a_liquidar']) && str_contains($val, '16.saldo'))) {
                        $colMap['saldo_a_liquidar'] = $i;
                        continue;
                    }

                    if (! isset($colMap['saldo_a_pagar']) && str_contains($val, 'saldo a pagar') || (! isset($colMap['saldo_a_pagar']) && str_contains($val, '17.saldo'))) {
                        $colMap['saldo_a_pagar'] = $i;
                        continue;
                    }

                    if (! isset($colMap['saldo_dotacao']) && (str_contains($val, 'saldo da dotação') || str_contains($val, 'saldo da dotacao') || str_contains($val, '20.saldo'))) {
                        $colMap['saldo_dotacao'] = $i;
                        continue;
                    }

                    if (! isset($colMap['saldo_disponivel']) && (str_contains($val, 'saldo disponível') || str_contains($val, 'saldo disponivel') || str_contains($val, '21.saldo'))) {
                        $colMap['saldo_disponivel'] = $i;
                        continue;
                    }
                }

                // Fallbacks
                if (! isset($colMap['valor_inicial'])) {
                    foreach ($vals as $i => $val) {
                        if (str_contains($val, 'inicial')) {
                            $colMap['valor_inicial'] = $i;
                            break;
                        }
                    }
                }

                if (! isset($colMap['empenhado'])) {
                    foreach ($vals as $i => $val) {
                        if (str_contains($val, 'empenhado')) {
                            $colMap['empenhado'] = $i;
                            break;
                        }
                    }
                }

                if (! isset($colMap['liquidado'])) {
                    foreach ($vals as $i => $val) {
                        if (str_contains($val, 'liquidado') || str_contains($val, 'pago')) {
                            $colMap['liquidado'] = $i;
                            break;
                        }
                    }
                }

                break;
            }
        }

        if ($headerIdx === null || ! isset($colMap['numero_despesa'])) {
            return;
        }

        foreach ($rows as $idx => $row) {
            if ($idx <= $headerIdx) {
                continue;
            }

            $v = $row->toArray();
            $numero = $v[$colMap['numero_despesa']] ?? null;

            if (! $numero || ! is_numeric($numero)) {
                continue;
            }

            $this->importedRows->push([
                'numero_despesa' => (int) $numero,
                'valor_inicial' => $this->colVal($v, $colMap, 'valor_inicial'),
                'empenhado' => $this->colVal($v, $colMap, 'empenhado'),
                'liquidado' => $this->colVal($v, $colMap, 'liquidado'),
                'credito_suplementar' => $this->colVal($v, $colMap, 'credito_suplementar'),
                'credito_especial' => $this->colVal($v, $colMap, 'credito_especial'),
                'reducao_creditos' => $this->colVal($v, $colMap, 'reducao_creditos'),
                'dotacao_atualizada' => $this->colVal($v, $colMap, 'dotacao_atualizada'),
                'pago' => $this->colVal($v, $colMap, 'pago'),
                'saldo_a_liquidar' => $this->colVal($v, $colMap, 'saldo_a_liquidar'),
                'saldo_a_pagar' => $this->colVal($v, $colMap, 'saldo_a_pagar'),
                'saldo_dotacao' => $this->colVal($v, $colMap, 'saldo_dotacao'),
                'saldo_disponivel' => $this->colVal($v, $colMap, 'saldo_disponivel'),
            ]);
        }
    }

    private function colVal(array $row, array $colMap, string $key): int
    {
        if (! isset($colMap[$key])) {
            return 0;
        }

        return $this->parseValor($row[$colMap[$key]] ?? 0);
    }

    private function parseValor(mixed $valor): int
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        if (is_object($valor)) {
            $valor = method_exists($valor, '__toString') ? (string) $valor : '';
        }

        if (is_string($valor)) {
            return $this->parseBrString($valor);
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

    private function parseBrString(string $raw): int
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
        $s = str_replace(["\xEF\xBC\x8C", "\xe2\x80\x9a"], ',', $s);
        $s = str_replace(["\xc2\xa0", "\xe2\x80\xaf", ' '], '', $s);

        if ($s === '') {
            return 0;
        }

        if (str_contains($s, ',')) {
            $lastComma = strrpos($s, ',');
            $parteInteira = str_replace('.', '', substr($s, 0, $lastComma));
            $parteInteira = preg_replace('/[^\d\-]/', '', $parteInteira);
            $parteFrac = substr(str_pad(preg_replace('/\D/', '', substr($s, $lastComma + 1)), 2, '0', STR_PAD_RIGHT), 0, 2);

            $total = ((int) $parteInteira) * 100 + (int) $parteFrac;

            return $neg ? -$total : $total;
        }

        $s = str_replace('.', '', $s);
        $s = preg_replace('/[^\d\-]/', '', $s);
        $total = ((int) $s) * 100;

        return $neg ? -$total : $total;
    }
}
