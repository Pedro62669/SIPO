<?php

namespace App\Imports;

use App\Enums\ParametrizacaoClassificacao;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;

class ParametrizacaoHistoricaImport implements ToCollection
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
            $vals = $row->map(fn ($v) => $this->normalizeHeader((string) ($v ?? '')))->toArray();

            $hasUnidade = $this->findColumn($vals, ['unidade', 'secretaria']) !== null;
            $hasValor = $this->findColumn($vals, ['valor liberado', 'valor', 'liberado']) !== null;

            if (! $hasUnidade || ! $hasValor) {
                continue;
            }

            $headerIdx = $idx;
            $colMap = [
                'unidade' => $this->findColumn($vals, ['unidade', 'secretaria']),
                'subunidade' => $this->findColumn($vals, ['subunidade', 'sub secretaria', 'subsecretaria']),
                'fonte' => $this->findColumn($vals, ['fonte de recurso', 'fonte recurso', 'fonte']),
                'classificacao' => $this->findColumn($vals, ['classificacao', 'classificação']),
                'percentual_anterior' => $this->findColumn($vals, ['percentual anterior', '% exercicio anterior', '% exercício anterior']),
                'valor_liberado' => $this->findColumn($vals, ['valor liberado', 'liberado', 'valor']),
            ];
            break;
        }

        if ($headerIdx === null || $colMap['unidade'] === null || $colMap['valor_liberado'] === null) {
            return;
        }

        foreach ($rows as $idx => $row) {
            if ($idx <= $headerIdx) {
                continue;
            }

            $v = $row->toArray();
            $unidade = trim((string) ($v[$colMap['unidade']] ?? ''));
            $valor = $v[$colMap['valor_liberado']] ?? null;

            if ($unidade === '' || $valor === null || $valor === '') {
                continue;
            }

            $this->importedRows->push([
                'unidade_raw' => $unidade,
                'subunidade_raw' => $colMap['subunidade'] !== null ? trim((string) ($v[$colMap['subunidade']] ?? '')) : '',
                'fonte_raw' => $colMap['fonte'] !== null ? trim((string) ($v[$colMap['fonte']] ?? '')) : '',
                'classificacao' => $this->parseClassificacao($colMap['classificacao'] !== null ? (string) ($v[$colMap['classificacao']] ?? '') : ''),
                'percentual_anterior' => $this->parsePercentual($colMap['percentual_anterior'] !== null ? ($v[$colMap['percentual_anterior']] ?? null) : null),
                'valor_liberado' => $this->parseValorParaCentavos($valor),
            ]);
        }
    }

    private function findColumn(array $headers, array $needles): ?int
    {
        foreach ($headers as $i => $header) {
            foreach ($needles as $needle) {
                if ($header !== '' && str_contains($header, $this->normalizeHeader($needle))) {
                    return $i;
                }
            }
        }

        return null;
    }

    private function normalizeHeader(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->ascii()
            ->replace(['_', '-', '.', ':'], ' ')
            ->squish()
            ->toString();
    }

    private function parseClassificacao(string $value): string
    {
        $normalized = $this->normalizeHeader($value);

        return match (true) {
            str_contains($normalized, 'pessoal') => ParametrizacaoClassificacao::Pessoal->value,
            str_contains($normalized, 'invest') => ParametrizacaoClassificacao::Investimento->value,
            str_contains($normalized, 'terceir') => ParametrizacaoClassificacao::Terceirizacao->value,
            str_contains($normalized, 'custeio') => ParametrizacaoClassificacao::Custeio->value,
            str_contains($normalized, 'geral') => ParametrizacaoClassificacao::Geral->value,
            default => ParametrizacaoClassificacao::Geral->value,
        };
    }

    private function parsePercentual(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $normalized = str_replace(['%', '.'], ['', ''], trim($value));
            $normalized = str_replace(',', '.', $normalized);
            return is_numeric($normalized) ? round((float) $normalized, 2) : null;
        }

        return is_numeric($value) ? round((float) $value, 2) : null;
    }

    private function parseValorParaCentavos(mixed $valor): int
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        if (is_object($valor)) {
            $valor = method_exists($valor, '__toString') ? (string) $valor : '';
        }

        if (is_string($valor)) {
            $s = trim($valor);
            $s = preg_replace('/^R\$\s*/iu', '', $s);
            $s = str_replace(["\xEF\xBC\x8C", "\xe2\x80\x9a"], ',', $s);
            $s = str_replace(["\xc2\xa0", "\xe2\x80\xaf", ' '], '', $s);

            if (str_contains($s, ',')) {
                $lastComma = strrpos($s, ',');
                $inteira = preg_replace('/[^\d\-]/', '', str_replace('.', '', substr($s, 0, $lastComma)));
                $fracao = substr(str_pad(preg_replace('/\D/', '', substr($s, $lastComma + 1)), 2, '0', STR_PAD_RIGHT), 0, 2);
                return ((int) $inteira) * 100 + (int) $fracao;
            }

            $s = preg_replace('/[^\d\-]/', '', str_replace('.', '', $s));
            return ((int) $s) * 100;
        }

        if (is_int($valor)) {
            return $valor * 100;
        }

        if (is_float($valor) || is_numeric($valor)) {
            return (int) round(((float) $valor) * 100);
        }

        return 0;
    }
}
