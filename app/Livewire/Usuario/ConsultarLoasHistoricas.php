<?php

namespace App\Livewire\Usuario;

use App\Models\DespesaImportada;
use App\Models\Orcamento;
use App\Models\Unidade;
use Livewire\Component;
use Livewire\WithPagination;

class ConsultarLoasHistoricas extends Component
{
    use WithPagination;

    public ?int $orcamento_id = null;

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
    }

    public function updatingBusca(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $unidadeId = auth()->user()->unidade_id;

        $orcamentosHistoricos = Orcamento::where('is_historico', true)
            ->orderByDesc('ano')
            ->get();

        $query = DespesaImportada::query()
            ->with(['unidade', 'subunidade', 'programa', 'acao', 'natureza', 'fonte']);

        if ($this->orcamento_id) {
            $query->where('orcamento_id', $this->orcamento_id);
        } else {
            $query->whereRaw('1 = 0');
        }

        if ($unidadeId) {
            $query->where('unidade_id', $unidadeId);
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

        $despesas = $query->orderBy('subunidade_id')->orderBy('acao_id')->paginate(50);

        return view('livewire.usuario.consultar-loas-historicas', [
            'orcamentosHistoricos' => $orcamentosHistoricos,
            'despesas' => $despesas,
            'totais' => $totais,
        ]);
    }
}
