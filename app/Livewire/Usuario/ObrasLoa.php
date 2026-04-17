<?php

namespace App\Livewire\Usuario;

use App\Enums\LoaAcaoStatus;
use App\Enums\TipoAcao;
use App\Models\LoaAcao;
use App\Models\LoaPreenchimento;
use App\Models\Orcamento;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ObrasLoa extends Component
{
    public int $orcamentoId;

    public ?int $subunidadeId = null;

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public string $newAcaoNome = '';

    public ?int $editingAcaoId = null;

    public string $editingNome = '';

    public function mount(int $orcamentoId): void
    {
        $this->orcamentoId = $orcamentoId;
        $unidade = auth()->user()->unidade;
        $firstSub = $unidade?->subunidades()->orderBy('codigo')->first();
        if ($firstSub) {
            $this->subunidadeId = $firstSub->id;
        }
    }

    public function openCreateModal(): void
    {
        $this->closeEditModal();
        $this->reset(['newAcaoNome']);
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
    }

    public function saveNewObra(): void
    {
        $this->validate([
            'subunidadeId' => ['required', Rule::exists('subunidades', 'id')->where('unidade_id', auth()->user()->unidade_id)],
            'newAcaoNome' => 'required|string|max:255',
        ]);

        LoaAcao::create([
            'orcamento_id' => $this->orcamentoId,
            'unidade_id' => auth()->user()->unidade_id,
            'subunidade_id' => $this->subunidadeId,
            'acao_original_id' => null,
            'tipo_acao' => TipoAcao::Obras->value,
            'nome' => $this->newAcaoNome,
            'status' => LoaAcaoStatus::Nova,
        ]);

        $this->closeCreateModal();
    }

    public function startEdit(int $id): void
    {
        $this->closeCreateModal();
        $acao = $this->queryObras()->findOrFail($id);
        if ($acao->status === LoaAcaoStatus::Excluida) {
            return;
        }
        $this->editingAcaoId = $id;
        $this->editingNome = $acao->nome;
        $this->showEditModal = true;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editingAcaoId' => 'required|exists:loa_acoes,id',
            'editingNome' => 'required|string|max:255',
        ]);

        $acao = $this->queryObras()->findOrFail($this->editingAcaoId);
        if ($acao->status === LoaAcaoStatus::Excluida) {
            $this->closeEditModal();

            return;
        }

        $acao->update([
            'nome_anterior' => $acao->nome,
            'nome' => $this->editingNome,
            'status' => LoaAcaoStatus::Editada,
        ]);

        $this->closeEditModal();
    }

    public function deleteAcao(int $id): void
    {
        $acao = $this->queryObras()->findOrFail($id);
        $acao->update(['status' => LoaAcaoStatus::Excluida]);
        if ($this->editingAcaoId === $id) {
            $this->closeEditModal();
        }
    }

    public function restoreAcao(int $id): void
    {
        $acao = $this->queryObras()->findOrFail($id);
        $acao->update([
            'status' => $acao->acao_original_id ? LoaAcaoStatus::Ativa : LoaAcaoStatus::Nova,
        ]);
    }

    private function queryObras()
    {
        return LoaAcao::where('orcamento_id', $this->orcamentoId)
            ->where('unidade_id', auth()->user()->unidade_id)
            ->where('subunidade_id', $this->subunidadeId)
            ->where('tipo_acao', TipoAcao::Obras);
    }

    public function render()
    {
        $unidade = auth()->user()->unidade;
        $subunidades = $unidade ? $unidade->subunidades()->orderBy('codigo')->get() : collect();

        $acoes = collect();
        $valorPorAcao = collect();

        if ($this->subunidadeId) {
            $acoes = LoaAcao::where('orcamento_id', $this->orcamentoId)
                ->where('unidade_id', $unidade?->id)
                ->where('subunidade_id', $this->subunidadeId)
                ->where('tipo_acao', TipoAcao::Obras)
                ->with('acaoOriginal.programa')
                ->orderBy('nome')
                ->get();

            if ($acoes->isNotEmpty()) {
                $valorPorAcao = LoaPreenchimento::query()
                    ->where('orcamento_id', $this->orcamentoId)
                    ->whereIn('loa_acao_id', $acoes->pluck('id'))
                    ->selectRaw('loa_acao_id, SUM(valor) as total')
                    ->groupBy('loa_acao_id')
                    ->pluck('total', 'loa_acao_id');
            }
        }

        return view('livewire.usuario.obras-loa', [
            'orcamento' => Orcamento::find($this->orcamentoId),
            'unidade' => $unidade,
            'subunidades' => $subunidades,
            'acoes' => $acoes,
            'valorPorAcao' => $valorPorAcao,
        ]);
    }
}
