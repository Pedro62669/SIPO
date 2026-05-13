<?php

namespace App\Livewire\Usuario;

use App\Enums\LoaAcaoStatus;
use App\Models\DespesaImportada;
use App\Models\FonteRecurso;
use App\Models\FonteUnidadeRestricao;
use App\Models\LoaAcao;
use App\Models\LoaPreenchimento;
use App\Models\Natureza;
use App\Models\Orcamento;
use App\Models\ParametrizacaoSecretaria;
use App\Models\Subunidade;
use App\Services\FonteVisibilidadeService;
use Livewire\Component;

class PreencherLoa extends Component
{
    public int $orcamentoId;

    public ?int $subunidadeId = null;

    public bool $showAddLine = false;

    public ?int $selectedLoaAcaoId = null;

    public ?int $naturezaId = null;

    public ?int $fonteId = null;

    public string $detalhamento = '';

    public $valor = 0;

    public string $observacao = '';

    public ?int $editingLineId = null;

    public $editingValor = 0;

    public string $editingObservacao = '';

    public function mount(int $orcamentoId): void
    {
        $this->orcamentoId = $orcamentoId;
        $unidade = auth()->user()->unidade;
        $firstSub = $unidade?->subunidades()->orderBy('codigo')->first();
        if ($firstSub) {
            $this->subunidadeId = $firstSub->id;
        }
        $this->initializeLoaAcoes();
    }

    public function updatedSubunidadeId(): void
    {
        $this->initializeLoaAcoes();
    }

    private function initializeLoaAcoes(): void
    {
        if (! $this->subunidadeId) {
            return;
        }

        $unidadeId = auth()->user()->unidade_id;
        if (! $unidadeId) {
            return;
        }

        $subunidade = Subunidade::where('id', $this->subunidadeId)
            ->where('unidade_id', $unidadeId)
            ->first();
        if (! $subunidade) {
            return;
        }

        $existingAcaoIds = LoaAcao::where('orcamento_id', $this->orcamentoId)
            ->where('unidade_id', $unidadeId)
            ->where('subunidade_id', $this->subunidadeId)
            ->pluck('acao_original_id')
            ->filter()
            ->toArray();

        $importedAcoes = DespesaImportada::where('orcamento_id', $this->orcamentoId)
            ->where('unidade_id', $unidadeId)
            ->where('subunidade_id', $this->subunidadeId)
            ->with('acao')
            ->get()
            ->pluck('acao')
            ->filter()
            ->unique('id');

        foreach ($importedAcoes as $acao) {
            if (! in_array($acao->id, $existingAcaoIds, true)) {
                LoaAcao::create([
                    'orcamento_id' => $this->orcamentoId,
                    'unidade_id' => $unidadeId,
                    'subunidade_id' => $this->subunidadeId,
                    'acao_original_id' => $acao->id,
                    'tipo_acao' => $acao->tipo_acao,
                    'nome' => $acao->descricao,
                    'status' => LoaAcaoStatus::Ativa,
                ]);
            }
        }
    }

    public function openAddLine(int $loaAcaoId): void
    {
        $this->selectedLoaAcaoId = $loaAcaoId;
        $this->showAddLine = true;
        $this->reset(['naturezaId', 'fonteId', 'detalhamento', 'valor', 'observacao']);
    }

    public function closeAddLine(): void
    {
        $this->showAddLine = false;
        $this->selectedLoaAcaoId = null;
    }

    public function saveLine(): void
    {
        $this->validate([
            'selectedLoaAcaoId' => 'required|exists:loa_acoes,id',
            'naturezaId' => 'required|exists:naturezas,id',
            'fonteId' => 'required|exists:fontes_recurso,id',
            'valor' => 'required|integer|min:0|max:999999999999',
        ]);

        $unidadeId = auth()->user()->unidade_id;
        $loaAcao = LoaAcao::where('id', $this->selectedLoaAcaoId)
            ->where('orcamento_id', $this->orcamentoId)
            ->where('unidade_id', $unidadeId)
            ->where('subunidade_id', $this->subunidadeId)
            ->first();
        if (! $loaAcao) {
            $this->addError('selectedLoaAcaoId', 'Ação inválida para esta subunidade.');

            return;
        }

        $exists = LoaPreenchimento::where('orcamento_id', $this->orcamentoId)
            ->where('loa_acao_id', $this->selectedLoaAcaoId)
            ->where('natureza_id', $this->naturezaId)
            ->where('fonte_id', $this->fonteId)
            ->exists();

        if ($exists) {
            $this->addError('naturezaId', 'Esta natureza + fonte já existe nesta ação.');

            return;
        }

        LoaPreenchimento::create([
            'orcamento_id' => $this->orcamentoId,
            'unidade_id' => $unidadeId,
            'subunidade_id' => $this->subunidadeId,
            'loa_acao_id' => $this->selectedLoaAcaoId,
            'natureza_id' => $this->naturezaId,
            'fonte_id' => $this->fonteId,
            'detalhamento' => $this->detalhamento ?: null,
            'valor' => $this->valor,
            'observacao' => $this->observacao ?: null,
        ]);

        $this->closeAddLine();
    }

    public function startEdit(int $lineId): void
    {
        $line = LoaPreenchimento::where('id', $lineId)
            ->where('orcamento_id', $this->orcamentoId)
            ->where('unidade_id', auth()->user()->unidade_id)
            ->firstOrFail();
        $this->editingLineId = $lineId;
        $this->editingValor = $line->valor;
        $this->editingObservacao = $line->observacao ?? '';
    }

    public function saveEdit(): void
    {
        $this->validate([
            'editingValor' => 'required|integer|min:0|max:999999999999',
        ]);

        $line = LoaPreenchimento::where('id', $this->editingLineId)
            ->where('orcamento_id', $this->orcamentoId)
            ->where('unidade_id', auth()->user()->unidade_id)
            ->firstOrFail();
        $line->update([
            'valor' => $this->editingValor,
            'observacao' => $this->editingObservacao ?: null,
        ]);

        $this->editingLineId = null;
    }

    public function cancelEdit(): void
    {
        $this->editingLineId = null;
        $this->editingValor = 0;
        $this->editingObservacao = '';
    }

    public function deleteLine(int $lineId): void
    {
        LoaPreenchimento::where('id', $lineId)
            ->where('orcamento_id', $this->orcamentoId)
            ->where('unidade_id', auth()->user()->unidade_id)
            ->delete();
    }

    private function getFilteredFontes()
    {
        $unidadeId = auth()->user()->unidade_id;
        $unidade = auth()->user()->unidade;

        $restricoes = FonteUnidadeRestricao::where('orcamento_id', $this->orcamentoId)->get();

        $restrictedFonteIds = [];
        $allowedRanges = [];

        foreach ($restricoes as $r) {
            if ($r->unidade_id == $unidadeId && (! $r->subunidade_id || $r->subunidade_id == $this->subunidadeId)) {
                $allowedRanges[] = [$r->fonte_recurso_inicio, $r->fonte_recurso_fim];
            } else {
                $restrictedFonteIds[] = [$r->fonte_recurso_inicio, $r->fonte_recurso_fim];
            }
        }

        $fontes = app(FonteVisibilidadeService::class)->fontesPermitidasParaUnidade(
            FonteRecurso::orderBy('codigo')->get(),
            $unidade
        );

        return $fontes->filter(function ($fonte) use ($restrictedFonteIds, $allowedRanges) {
                $code = $fonte->codigo;

                foreach ($restrictedFonteIds as [$start, $end]) {
                    if ($code >= $start && $code <= $end) {
                        foreach ($allowedRanges as [$aStart, $aEnd]) {
                            if ($code >= $aStart && $code <= $aEnd) {
                                return true;
                            }
                        }

                        return false;
                    }
                }
                return true;
            })->values();
    }

    public function render()
    {
        $unidade = auth()->user()->unidade;
        $subunidades = $unidade ? $unidade->subunidades()->orderBy('codigo')->get() : collect();

        $loaAcoes = collect();
        $preenchimentos = collect();
        $totalPreenchido = 0;
        $totalLiberadoCentavos = 0;
        $totalPreenchidoCentavos = 0;

        if ($this->subunidadeId) {
            $loaAcoes = LoaAcao::where('orcamento_id', $this->orcamentoId)
                ->where('unidade_id', $unidade?->id)
                ->where('subunidade_id', $this->subunidadeId)
                ->whereNot('status', LoaAcaoStatus::Excluida)
                ->with('acaoOriginal.programa')
                ->orderBy('nome')
                ->get();

            $preenchimentos = LoaPreenchimento::where('orcamento_id', $this->orcamentoId)
                ->where('subunidade_id', $this->subunidadeId)
                ->with(['natureza', 'fonte', 'loaAcao'])
                ->get()
                ->groupBy('loa_acao_id');

            // valor_liberado: centavos; LoaPreenchimento.valor: reais inteiros — converte para centavos para comparar.
            $totalPreenchido = (int) LoaPreenchimento::where('orcamento_id', $this->orcamentoId)
                ->where('unidade_id', $unidade?->id)
                ->sum('valor');

            $totalLiberadoCentavos = (int) ParametrizacaoSecretaria::where('orcamento_id', $this->orcamentoId)
                ->where('unidade_id', $unidade?->id)
                ->sum('valor_liberado');

            $totalPreenchidoCentavos = $totalPreenchido * 100;
        }

        $percentualExecucao = $totalLiberadoCentavos > 0
            ? round(($totalPreenchidoCentavos / $totalLiberadoCentavos) * 100)
            : 0;

        return view('livewire.usuario.preencher-loa', [
            'orcamento' => Orcamento::find($this->orcamentoId),
            'unidade' => $unidade,
            'subunidades' => $subunidades,
            'loaAcoes' => $loaAcoes,
            'preenchimentos' => $preenchimentos,
            'naturezas' => Natureza::orderBy('codigo')->get(),
            'fontes' => $this->getFilteredFontes(),
            'totalPreenchido' => $totalPreenchido,
            'totalLiberadoCentavos' => $totalLiberadoCentavos,
            'totalPreenchidoCentavos' => $totalPreenchidoCentavos,
            'percentualExecucao' => $percentualExecucao,
        ]);
    }
}
