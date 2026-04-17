<?php

namespace App\Livewire\Admin;

use App\Models\Orcamento;
use Livewire\Component;

class BotaoExcluirOrcamento extends Component
{
    public int $orcamentoId;

    public string $tipo;

    public int $ano;

    /** Nome da rota Laravel para redirecionar após exclusão (opcional). */
    public ?string $aposExcluirRota = null;

    public function excluir(): void
    {
        if (! auth()->user()?->hasRole('admin')) {
            abort(403);
        }

        $orcamento = Orcamento::query()->findOrFail($this->orcamentoId);
        $orcamento->delete();

        session()->flash('status', "Orçamento {$this->tipo} {$this->ano} excluído com sucesso.");

        if ($this->aposExcluirRota) {
            $this->redirectRoute($this->aposExcluirRota, navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.admin.botao-excluir-orcamento');
    }
}
