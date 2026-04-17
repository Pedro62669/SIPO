<?php

namespace App\Livewire\Usuario;

use App\Enums\EnvioOrcamentoStatus;
use App\Enums\ParametrizacaoClassificacao;
use App\Models\EnvioOrcamento;
use App\Models\LoaPreenchimento;
use App\Models\Orcamento;
use App\Models\ParametrizacaoSecretaria;
use Livewire\Component;

class EnviarOrcamento extends Component
{
    public int $orcamentoId;

    public function mount(int $orcamentoId): void
    {
        $this->orcamentoId = $orcamentoId;
    }

    public function enviar(): void
    {
        $unidadeId = auth()->user()->unidade_id;
        if (! $unidadeId) {
            session()->flash('error', 'Usuário sem unidade vinculada.');

            return;
        }

        EnvioOrcamento::updateOrCreate(
            [
                'orcamento_id' => $this->orcamentoId,
                'unidade_id' => $unidadeId,
            ],
            [
                'status' => EnvioOrcamentoStatus::Enviado,
                'enviado_em' => now(),
                'user_id' => auth()->id(),
            ]
        );

        session()->flash('status', 'Orçamento enviado com sucesso.');
    }

    private function classificacaoForLine(LoaPreenchimento $line): string
    {
        $param = ParametrizacaoSecretaria::query()
            ->where('orcamento_id', $line->orcamento_id)
            ->where('unidade_id', $line->unidade_id)
            ->where('fonte_id', $line->fonte_id)
            ->where(function ($q) use ($line) {
                $q->whereNull('subunidade_id')
                    ->orWhere('subunidade_id', $line->subunidade_id);
            })
            ->orderByRaw('CASE WHEN subunidade_id IS NOT NULL THEN 0 ELSE 1 END')
            ->first();

        if ($param) {
            return $param->classificacao->value;
        }

        $raw = $line->natureza?->classificacao;
        if (is_string($raw) && ($enum = ParametrizacaoClassificacao::tryFrom($raw))) {
            return $enum->value;
        }

        return 'outros';
    }

    public function render()
    {
        $unidadeId = auth()->user()->unidade_id;
        $orcamento = Orcamento::find($this->orcamentoId);

        $liberado = collect();
        $totalLiberado = 0;
        if ($unidadeId) {
            $params = ParametrizacaoSecretaria::where('orcamento_id', $this->orcamentoId)
                ->where('unidade_id', $unidadeId)
                ->get();
            $liberado = $params->groupBy(fn ($p) => $p->classificacao->value)
                ->map(fn ($group) => (int) $group->sum('valor_liberado'));
            $totalLiberado = (int) $params->sum('valor_liberado');
        }

        $preenchimentos = collect();
        $preenchidoPorClass = [];
        if ($unidadeId) {
            $preenchimentos = LoaPreenchimento::where('orcamento_id', $this->orcamentoId)
                ->where('unidade_id', $unidadeId)
                ->with(['natureza', 'fonte', 'subunidade'])
                ->get();

            // valor_liberado (parametrização) está em centavos; LoaPreenchimento.valor em reais inteiros.
            foreach ($preenchimentos as $line) {
                $key = $this->classificacaoForLine($line);
                $preenchidoPorClass[$key] = ($preenchidoPorClass[$key] ?? 0) + ((int) $line->valor * 100);
            }
        }

        $preenchido = collect($preenchidoPorClass);
        $totalPreenchido = (int) $preenchimentos->sum(fn (LoaPreenchimento $line) => (int) $line->valor * 100);

        $keys = $liberado->keys()
            ->merge($preenchido->keys())
            ->unique()
            ->sort()
            ->values();

        $rows = $keys->map(function (string $key) use ($liberado, $preenchido) {
            $lib = (int) ($liberado[$key] ?? 0);
            $pre = (int) ($preenchido[$key] ?? 0);

            return [
                'classificacao' => $key,
                'liberado_centavos' => $lib,
                'preenchido_centavos' => $pre,
                'diferenca_centavos' => $lib - $pre,
            ];
        });

        $envio = null;
        if ($unidadeId) {
            $envio = EnvioOrcamento::where('orcamento_id', $this->orcamentoId)
                ->where('unidade_id', $unidadeId)
                ->first();
        }

        return view('livewire.usuario.enviar-orcamento', [
            'orcamento' => $orcamento,
            'unidade' => auth()->user()->unidade,
            'rows' => $rows,
            'totalLiberadoCentavos' => $totalLiberado,
            'totalPreenchidoCentavos' => $totalPreenchido,
            'envio' => $envio,
        ]);
    }
}
