<?php

namespace App\Services;

use App\Enums\ParametrizacaoClassificacao;
use App\Models\Acao;
use App\Models\DespesaImportada;
use App\Models\FonteRecurso;
use App\Models\Natureza;
use App\Models\Orcamento;
use App\Models\ParametrizacaoSecretaria;
use App\Models\Programa;
use App\Models\RegraFonte;
use App\Models\Subunidade;
use App\Models\Unidade;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportacaoService
{
    /**
     * @return array{created: int, duplicates: int, errors: list<string>}
     */
    public function processarDespesas(int $orcamentoId, Collection $rows, bool $aplicarRegrasFonte = true): array
    {
        Orcamento::findOrFail($orcamentoId);
        $regrasFonte = $aplicarRegrasFonte
            ? RegraFonte::where('orcamento_id', $orcamentoId)->pluck('fonte_destino', 'fonte_origem')
            : collect();

        $created = 0;
        $duplicates = 0;
        $errors = [];
        $seen = [];

        foreach ($rows as $row) {
            try {
                $unidade = Unidade::firstOrCreate(
                    ['codigo' => $row['unidade_codigo']],
                    ['descricao' => $row['unidade_descricao']]
                );

                $subunidade = Subunidade::firstOrCreate(
                    ['unidade_id' => $unidade->id, 'codigo' => $row['subunidade_codigo']],
                    ['descricao' => $row['subunidade_descricao']]
                );

                $programa = Programa::firstOrCreate(
                    ['codigo' => $row['programa_codigo']],
                    ['descricao' => $row['programa_descricao']]
                );

                $acao = Acao::firstOrCreate(
                    ['programa_id' => $programa->id, 'codigo' => $row['acao_codigo']],
                    ['descricao' => $row['acao_descricao']]
                );

                $natureza = Natureza::firstOrCreate(
                    ['codigo' => $row['natureza_codigo']],
                    ['descricao' => $row['natureza_codigo']]
                );

                $fonteCodigo = $row['fonte_codigo'];
                if ($regrasFonte->has($fonteCodigo)) {
                    $fonteCodigo = $regrasFonte->get($fonteCodigo);
                }

                $fonte = FonteRecurso::firstOrCreate(
                    ['codigo' => $fonteCodigo],
                    ['descricao' => 'Fonte '.$fonteCodigo]
                );

                $key = "{$acao->id}:{$natureza->id}:{$fonte->id}";
                if (isset($seen[$key])) {
                    $duplicates++;

                    continue;
                }
                $seen[$key] = true;

                DespesaImportada::create([
                    'orcamento_id' => $orcamentoId,
                    'ano' => $row['ano'],
                    'numero_despesa' => $row['numero_despesa'],
                    'natureza_id' => $natureza->id,
                    'fonte_id' => $fonte->id,
                    'unidade_id' => $unidade->id,
                    'subunidade_id' => $subunidade->id,
                    'programa_id' => $programa->id,
                    'acao_id' => $acao->id,
                    'valor_inicial' => 0,
                    'empenhado' => 0,
                    'liquidado' => 0,
                ]);

                $created++;
            } catch (\Exception $e) {
                $errors[] = "Linha despesa #{$row['numero_despesa']}: {$e->getMessage()}";
            }
        }

        return [
            'created' => $created,
            'duplicates' => $duplicates,
            'errors' => $errors,
        ];
    }

    /**
     * @return array{updated: int, not_found: int, errors: list<string>}
     */
    public function processarSaldos(int $orcamentoId, Collection $rows): array
    {
        $updated = 0;
        $notFound = 0;
        $errors = [];

        foreach ($rows as $row) {
            try {
                $despesa = DespesaImportada::where('orcamento_id', $orcamentoId)
                    ->where('numero_despesa', $row['numero_despesa'])
                    ->first();

                if (! $despesa) {
                    $notFound++;
                    continue;
                }

                $despesa->update([
                    'valor_inicial' => $row['valor_inicial'],
                    'empenhado' => $row['empenhado'],
                    'liquidado' => $row['liquidado'],
                    'credito_suplementar' => $row['credito_suplementar'] ?? 0,
                    'credito_especial' => $row['credito_especial'] ?? 0,
                    'total_creditos_adicionais' => $row['total_creditos_adicionais'] ?? 0,
                    'reducao_creditos' => $row['reducao_creditos'] ?? 0,
                    'dotacao_atualizada' => $row['dotacao_atualizada'] ?? 0,
                    'pago' => $row['pago'] ?? 0,
                    'saldo_a_liquidar' => $row['saldo_a_liquidar'] ?? 0,
                    'saldo_a_pagar' => $row['saldo_a_pagar'] ?? 0,
                    'saldo_dotacao' => $row['saldo_dotacao'] ?? 0,
                    'saldo_disponivel' => $row['saldo_disponivel'] ?? 0,
                ]);

                $updated++;
            } catch (\Exception $e) {
                $errors[] = "Despesa #{$row['numero_despesa']}: {$e->getMessage()}";
            }
        }

        return [
            'updated' => $updated,
            'not_found' => $notFound,
            'errors' => $errors,
        ];
    }

    /**
     * @return array{created: int, errors: list<string>}
     */
    public function processarParametrizacoesHistoricas(int $orcamentoId, Collection $rows): array
    {
        Orcamento::findOrFail($orcamentoId);

        $created = 0;
        $errors = [];
        $payload = [];

        foreach ($rows as $index => $row) {
            try {
                $unidade = $this->resolverUnidade((string) Arr::get($row, 'unidade_raw', ''));
                if (! $unidade) {
                    throw new \RuntimeException('Unidade não encontrada.');
                }

                $subunidade = $this->resolverSubunidade(
                    $unidade->id,
                    (string) Arr::get($row, 'subunidade_raw', '')
                );

                $fonte = $this->resolverFonte((string) Arr::get($row, 'fonte_raw', ''));
                if (! $fonte) {
                    throw new \RuntimeException('Fonte de recurso não encontrada.');
                }

                $payload[] = [
                    'orcamento_id' => $orcamentoId,
                    'unidade_id' => $unidade->id,
                    'subunidade_id' => $subunidade?->id,
                    'fonte_id' => $fonte->id,
                    'classificacao' => ParametrizacaoClassificacao::tryFrom((string) Arr::get($row, 'classificacao'))
                        ?->value ?? ParametrizacaoClassificacao::Geral->value,
                    'percentual_anterior' => Arr::get($row, 'percentual_anterior'),
                    'valor_liberado' => (int) Arr::get($row, 'valor_liberado', 0),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $created++;
            } catch (\Throwable $e) {
                $errors[] = 'Linha '.($index + 2).': '.$e->getMessage();
            }
        }

        if ($payload !== []) {
            DB::transaction(function () use ($orcamentoId, $payload) {
                ParametrizacaoSecretaria::where('orcamento_id', $orcamentoId)->delete();
                ParametrizacaoSecretaria::insert($payload);
            });
        }

        return [
            'created' => $created,
            'errors' => $errors,
        ];
    }

    public function criarRegrasSubstituicaoFontePadrao(int $orcamentoId): void
    {
        $regras = [
            '2500' => '1500',
            '2501' => '1501',
            '2660' => '1660',
            '2661' => '1661',
            '2700' => '1700',
            '2703' => '1703',
            '2708' => '1708',
        ];

        foreach ($regras as $origem => $destino) {
            FonteRecurso::firstOrCreate(
                ['codigo' => $origem],
                ['descricao' => 'Fonte '.$origem]
            );

            FonteRecurso::firstOrCreate(
                ['codigo' => $destino],
                ['descricao' => 'Fonte '.$destino]
            );

            RegraFonte::firstOrCreate(
                ['orcamento_id' => $orcamentoId, 'fonte_origem' => $origem],
                ['fonte_destino' => $destino]
            );
        }
    }

    private function resolverUnidade(string $raw): ?Unidade
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^\D*(\d+)/', $raw, $matches)) {
            $codigo = (int) $matches[1];
            $unidade = Unidade::where('codigo', $codigo)->first();
            if ($unidade) {
                return $unidade;
            }
        }

        $descricao = Str::of($raw)->ascii()->lower()->squish()->toString();

        return Unidade::query()
            ->get()
            ->first(function (Unidade $unidade) use ($descricao) {
                $atual = Str::of((string) $unidade->descricao)->ascii()->lower()->squish()->toString();
                return $atual === $descricao;
            });
    }

    private function resolverSubunidade(int $unidadeId, string $raw): ?Subunidade
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/^\D*(\d+)/', $raw, $matches)) {
            $codigo = (int) $matches[1];
            $sub = Subunidade::where('unidade_id', $unidadeId)->where('codigo', $codigo)->first();
            if ($sub) {
                return $sub;
            }
        }

        $descricao = Str::of($raw)->ascii()->lower()->squish()->toString();

        return Subunidade::query()
            ->where('unidade_id', $unidadeId)
            ->get()
            ->first(function (Subunidade $subunidade) use ($descricao) {
                $atual = Str::of((string) $subunidade->descricao)->ascii()->lower()->squish()->toString();
                return $atual === $descricao;
            });
    }

    private function resolverFonte(string $raw): ?FonteRecurso
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        preg_match('/(\d{3,10})/', $raw, $matches);
        $codigo = trim((string) ($matches[1] ?? $raw));

        if ($codigo === '') {
            return null;
        }

        return FonteRecurso::firstOrCreate(
            ['codigo' => $codigo],
            ['descricao' => 'Fonte '.$codigo]
        );
    }
}
