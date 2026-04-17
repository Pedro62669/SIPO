<?php

namespace App\Livewire\Usuario;

use App\Enums\LoaAcaoStatus;
use App\Enums\TipoAcao;
use App\Models\LoaAcao;
use App\Models\Orcamento;
use App\Models\Subunidade;
use Illuminate\Validation\Rule;
use Livewire\Component;

class MetasAcoes extends Component
{
    public int $orcamentoId;

    public ?int $subunidadeId = null;

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public string $newAcaoNome = '';

    public string $newAcaoTipo = '2';

    public ?int $editingAcaoId = null;

    public string $editingNome = '';

    public string $editingTipo = '2';

    public function mount(int $orcamentoId): void
    {
        $this->orcamentoId = $orcamentoId;
        $unidade = auth()->user()->unidade;
        $firstSub = $unidade?->subunidades()->orderBy('codigo')->first();
        if ($firstSub) {
            $this->subunidadeId = $firstSub->id;
        }
        $this->newAcaoTipo = TipoAcao::Atividade->value;
    }

    public function openCreateModal(): void
    {
        $this->closeEditModal();
        $this->reset(['newAcaoNome']);
        $this->newAcaoTipo = TipoAcao::Atividade->value;
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingAcaoId = null;
        $this->editingNome = '';
        $this->editingTipo = TipoAcao::Atividade->value;
    }

    public function saveNewAcao(): void
    {
        $this->validate([
            'subunidadeId' => ['required', Rule::exists('subunidades', 'id')->where('unidade_id', auth()->user()->unidade_id)],
            'newAcaoNome' => 'required|string|max:255',
            'newAcaoTipo' => ['required', Rule::in(['0', '1', '2'])],
        ]);

        LoaAcao::create([
            'orcamento_id' => $this->orcamentoId,
            'unidade_id' => auth()->user()->unidade_id,
            'subunidade_id' => $this->subunidadeId,
            'acao_original_id' => null,
            'tipo_acao' => $this->newAcaoTipo,
            'nome' => $this->newAcaoNome,
            'status' => LoaAcaoStatus::Nova,
        ]);

        $this->closeCreateModal();
    }

    public function startEdit(int $id): void
    {
        $this->closeCreateModal();
        $acao = $this->queryAcoes()->findOrFail($id);
        if ($acao->status === LoaAcaoStatus::Excluida) {
            return;
        }
        $this->editingAcaoId = $id;
        $this->editingNome = $acao->nome;
        $this->editingTipo = $acao->tipo_acao?->value ?? TipoAcao::Atividade->value;
        $this->showEditModal = true;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editingAcaoId' => 'required|exists:loa_acoes,id',
            'editingNome' => 'required|string|max:255',
            'editingTipo' => ['required', Rule::in(['0', '1', '2'])],
        ]);

        $acao = $this->queryAcoes()->findOrFail($this->editingAcaoId);
        if ($acao->status === LoaAcaoStatus::Excluida) {
            $this->closeEditModal();

            return;
        }

        $acao->update([
            'nome_anterior' => $acao->nome,
            'nome' => $this->editingNome,
            'tipo_acao' => $this->editingTipo,
            'status' => LoaAcaoStatus::Editada,
        ]);

        $this->closeEditModal();
    }

    public function deleteAcao(int $id): void
    {
        $acao = $this->queryAcoes()->findOrFail($id);
        $acao->update(['status' => LoaAcaoStatus::Excluida]);
        if ($this->editingAcaoId === $id) {
            $this->closeEditModal();
        }
    }

    public function restoreAcao(int $id): void
    {
        $acao = $this->queryAcoes()->findOrFail($id);
        $acao->update([
            'status' => $acao->acao_original_id ? LoaAcaoStatus::Ativa : LoaAcaoStatus::Nova,
        ]);
    }

    private function queryAcoes()
    {
        return LoaAcao::where('orcamento_id', $this->orcamentoId)
            ->where('unidade_id', auth()->user()->unidade_id)
            ->where('subunidade_id', $this->subunidadeId);
    }

    public function render()
    {
        $unidade = auth()->user()->unidade;
        $subunidades = $unidade ? $unidade->subunidades()->orderBy('codigo')->get() : collect();

        $acoes = collect();
        if ($this->subunidadeId) {
            $acoes = LoaAcao::where('orcamento_id', $this->orcamentoId)
                ->where('unidade_id', $unidade?->id)
                ->where('subunidade_id', $this->subunidadeId)
                ->with('acaoOriginal.programa')
                ->orderBy('nome')
                ->get();
        }

        return view('livewire.usuario.metas-acoes', [
            'orcamento' => Orcamento::find($this->orcamentoId),
            'unidade' => $unidade,
            'subunidades' => $subunidades,
            'acoes' => $acoes,
            'tiposAcao' => TipoAcao::cases(),
        ]);
    }
}
