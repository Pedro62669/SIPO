<?php

namespace App\Livewire\Admin;

use App\Models\Orcamento;
use Livewire\Component;
use Livewire\WithPagination;

class ListaOrcamentosPainel extends Component
{
    use WithPagination;

    public function excluir(int $orcamentoId): void
    {
        if (! auth()->user()?->hasRole('admin')) {
            abort(403);
        }

        $orcamento = Orcamento::query()->findOrFail($orcamentoId);

        $label = $orcamento->tipo->value.' '.$orcamento->ano;
        $orcamento->delete();

        session()->flash('status', "Orçamento {$label} excluído com sucesso.");

        $this->resetPage();
    }

    public function render()
    {
        $orcamentos = Orcamento::query()
            ->where('is_historico', false)
            ->latest('created_at')
            ->paginate(15);

        return view('livewire.admin.lista-orcamentos-painel', [
            'orcamentos' => $orcamentos,
        ]);
    }
}
