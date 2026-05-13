<?php

namespace App\Livewire\Admin;

use App\Models\DespesaImportada;
use App\Models\Orcamento;
use App\Models\ParametrizacaoSecretaria;
use App\Models\Unidade;
use Livewire\Component;
use Livewire\WithPagination;

class ConsultarLoas extends Component
{
    use WithPagination;

    public ?int $orcamento_id = null;

    public ?int $unidade_id = null;

    public string $busca = '';

    public function mount(): void
    {
        $primeiro = Orcamento::where('is_historico', true)
            ->orderByDesc('ano')
            ->first();

        if ($primeiro) {
            $this->orcamento_id = $primeiro->id;
        }
    }

    public function updatingOrcamentoId(): void
    {
        $this->resetPage();
        $this->unidade_id = null;
    }

    public function updatingUnidadeId(): void
    {
        $this->resetPage();
    }

    public function updatingBusca(): void
    {
        $this->resetPage();
    }

    public function excluir(int $orcamentoId): void
    {
        if (! auth()->user()?->hasRole('admin')) {
            abort(403);
        }

        $orcamento = Orcamento::where('id', $orcamentoId)
            ->where('is_historico', true)
            ->firstOrFail();

        $label = 'LOA '.$orcamento->ano;
        $orcamento->delete();

        session()->flash('status', "{$label} excluída com sucesso.");

        $this->orcamento_id = null;
        $this->unidade_id = null;
        $this->busca = '';
        $this->resetPage();
    }

    public function render()
    {
        $orcamentosHistoricos = Orcamento::where('is_historico', true)
            ->orderByDesc('ano')
            ->get();

        $unidades = $this->orcamento_id
            ? Unidade::whereHas('despesasImportadas', fn ($q) => $q->where('orcamento_id', $this->orcamento_id))
                ->orderBy('descricao')
                ->get()
            : collect();

        $query = DespesaImportada::query()
            ->with(['unidade', 'subunidade', 'programa', 'acao', 'natureza', 'fonte']);

        if ($this->orcamento_id) {
            $query->where('orcamento_id', $this->orcamento_id);
        } else {
            $query->whereRaw('1 = 0');
        }

        if ($this->unidade_id) {
            $query->where('unidade_id', $this->unidade_id);
        }

        if ($this->busca !== '') {
            $busca = $this->busca;
            $query->where(function ($q) use ($busca) {
                $q->whereHas('acao', fn ($s) => $s->where('descricao', 'like', "%{$busca}%"))
                    ->orWhereHas('natureza', fn ($s) => $s->where('descricao', 'like', "%{$busca}%"))
                    ->orWhereHas('programa', fn ($s) => $s->where('descricao', 'like', "%{$busca}%"));
            });
        }

        $totais = (clone $query)->selectRaw(
            'SUM(valor_inicial) as total_inicial, '.
            'SUM(empenhado) as total_empenhado, '.
            'SUM(liquidado) as total_liquidado, '.
            'SUM(dotacao_atualizada) as total_dotacao_atualizada, '.
            'SUM(pago) as total_pago, '.
            'SUM(saldo_dotacao) as total_saldo_dotacao'
        )->first();

        $paramQuery = ParametrizacaoSecretaria::query()
            ->with(['unidade', 'subunidade', 'fonte']);

        if ($this->orcamento_id) {
            $paramQuery->where('orcamento_id', $this->orcamento_id);
        } else {
            $paramQuery->whereRaw('1 = 0');
        }

        if ($this->unidade_id) {
            $paramQuery->where('unidade_id', $this->unidade_id);
        }

        $parametrizacoes = $paramQuery
            ->orderBy('unidade_id')
            ->orderBy('subunidade_id')
            ->get();

        $temParametrizacao = $parametrizacoes->isNotEmpty();
        $totalLiberadoParametrizado = (int) $parametrizacoes->sum('valor_liberado');
        $totalLiberado = $temParametrizacao
            ? $totalLiberadoParametrizado
            : (int) ($totais->total_dotacao_atualizada ?? 0);

        $saldoDisponivel = (int) ($totais->total_saldo_dotacao
            ?? (($totais->total_dotacao_atualizada ?? 0) - ($totais->total_empenhado ?? 0)));

        $paramTotais = [
            'total_liberado' => $totalLiberado,
            'saldo_vs_empenhado' => $temParametrizacao
                ? $totalLiberadoParametrizado - (int) ($totais->total_empenhado ?? 0)
                : $saldoDisponivel,
        ];

        $despesas = $query->orderBy('unidade_id')->orderBy('subunidade_id')->orderBy('acao_id')->paginate(50);

        return view('livewire.admin.consultar-loas', [
            'orcamentosHistoricos' => $orcamentosHistoricos,
            'unidades' => $unidades,
            'despesas' => $despesas,
            'totais' => $totais,
            'parametrizacoes' => $parametrizacoes,
            'paramTotais' => $paramTotais,
        ]);
    }
}
