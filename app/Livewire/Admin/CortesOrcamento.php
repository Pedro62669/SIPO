<?php

namespace App\Livewire\Admin;

use App\Enums\ParametrizacaoClassificacao;
use App\Models\Corte;
use App\Models\LoaPreenchimento;
use App\Models\Orcamento;
use App\Models\ParametrizacaoSecretaria;
use App\Models\Unidade;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;

class CortesOrcamento extends Component
{
    use WithPagination;

    public int $orcamentoId;

    public ?int $filtroUnidadeId = null;

    public bool $showCorteModal = false;

    public ?int $cortePreenchimentoId = null;

    public int $corteValorAtual = 0;

    public int $corteNovoValor = 0;

    public string $corteJustificativa = '';

    public function mount(int $orcamentoId): void
    {
        if (Orcamento::whereKey($orcamentoId)->where('is_historico', true)->exists()) {
            abort(403, 'LOAs anteriores são somente para consulta.');
        }

        $this->orcamentoId = $orcamentoId;
    }

    public function updatedFiltroUnidadeId(): void
    {
        $this->resetPage();
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

    public function openCorteModal(int $preenchimentoId): void
    {
        $line = LoaPreenchimento::query()
            ->where('orcamento_id', $this->orcamentoId)
            ->findOrFail($preenchimentoId);

        $this->cortePreenchimentoId = $line->id;
        $this->corteValorAtual = (int) $line->valor;
        $this->corteNovoValor = (int) $line->valor;
        $this->corteJustificativa = '';
        $this->showCorteModal = true;
    }

    public function closeCorteModal(): void
    {
        $this->showCorteModal = false;
        $this->cortePreenchimentoId = null;
        $this->corteValorAtual = 0;
        $this->corteNovoValor = 0;
        $this->corteJustificativa = '';
    }

    public function aplicarCorte(): void
    {
        $this->validate([
            'cortePreenchimentoId' => ['required', Rule::exists('loa_preenchimentos', 'id')->where('orcamento_id', $this->orcamentoId)],
            'corteNovoValor' => ['required', 'integer', 'min:0'],
            'corteJustificativa' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function (): void {
            $line = LoaPreenchimento::query()
                ->where('orcamento_id', $this->orcamentoId)
                ->lockForUpdate()
                ->findOrFail($this->cortePreenchimentoId);

            $antes = (int) $line->valor;

            if ($this->corteNovoValor > $antes) {
                throw ValidationException::withMessages([
                    'corteNovoValor' => 'O novo valor não pode ser maior que o valor atual da linha.',
                ]);
            }

            if ($this->corteNovoValor === $antes) {
                throw ValidationException::withMessages([
                    'corteNovoValor' => 'Informe um valor menor que o atual para registrar um corte.',
                ]);
            }

            $line->update(['valor' => $this->corteNovoValor]);

            Corte::updateOrCreate(
                ['loa_preenchimento_id' => $line->id],
                [
                    'orcamento_id' => $this->orcamentoId,
                    'valor_original' => $antes,
                    'valor_cortado' => $this->corteNovoValor,
                    'justificativa' => $this->corteJustificativa !== '' ? $this->corteJustificativa : null,
                    'admin_id' => auth()->id(),
                ]
            );
        });

        session()->flash('status', 'Corte aplicado e linha do LOA atualizada.');
        $this->closeCorteModal();
        $this->resetPage();
    }

    /**
     * @return Collection<int, array{unidade: Unidade, liberado_centavos: int, preenchido_centavos: int, diferenca_centavos: int}>
     */
    private function resumoPorUnidade(): Collection
    {
        return Unidade::query()
            ->orderBy('descricao')
            ->get()
            ->map(function (Unidade $u) {
                $lib = (int) ParametrizacaoSecretaria::query()
                    ->where('orcamento_id', $this->orcamentoId)
                    ->where('unidade_id', $u->id)
                    ->sum('valor_liberado');

                $pre = (int) LoaPreenchimento::query()
                    ->where('orcamento_id', $this->orcamentoId)
                    ->where('unidade_id', $u->id)
                    ->get()
                    ->sum(fn (LoaPreenchimento $l) => (int) $l->valor * 100);

                return [
                    'unidade' => $u,
                    'liberado_centavos' => $lib,
                    'preenchido_centavos' => $pre,
                    'diferenca_centavos' => $lib - $pre,
                ];
            })
            ->filter(fn (array $row) => $row['liberado_centavos'] > 0 || $row['preenchido_centavos'] > 0)
            ->values();
    }

    /**
     * @return Collection<int, array{classificacao: string, liberado_centavos: int, preenchido_centavos: int, diferenca_centavos: int}>
     */
    private function resumoPorClassificacao(): Collection
    {
        $params = ParametrizacaoSecretaria::where('orcamento_id', $this->orcamentoId)->get();
        $liberado = $params->groupBy(fn ($p) => $p->classificacao->value)
            ->map(fn ($group) => (int) $group->sum('valor_liberado'));

        $preenchidoPorClass = [];
        $preenchimentos = LoaPreenchimento::where('orcamento_id', $this->orcamentoId)
            ->with('natureza')
            ->get();

        foreach ($preenchimentos as $line) {
            $key = $this->classificacaoForLine($line);
            $preenchidoPorClass[$key] = ($preenchidoPorClass[$key] ?? 0) + ((int) $line->valor * 100);
        }

        $preenchido = collect($preenchidoPorClass);

        $keys = $liberado->keys()
            ->merge($preenchido->keys())
            ->unique()
            ->sort()
            ->values();

        return $keys->map(function (string $key) use ($liberado, $preenchido) {
            $lib = (int) ($liberado[$key] ?? 0);
            $pre = (int) ($preenchido[$key] ?? 0);

            return [
                'classificacao' => $key,
                'liberado_centavos' => $lib,
                'preenchido_centavos' => $pre,
                'diferenca_centavos' => $lib - $pre,
            ];
        });
    }

    private function totaisGlobais(): array
    {
        $lib = (int) ParametrizacaoSecretaria::where('orcamento_id', $this->orcamentoId)->sum('valor_liberado');
        $pre = (int) LoaPreenchimento::where('orcamento_id', $this->orcamentoId)
            ->get()
            ->sum(fn (LoaPreenchimento $l) => (int) $l->valor * 100);

        return [
            'liberado_centavos' => $lib,
            'preenchido_centavos' => $pre,
            'diferenca_centavos' => $lib - $pre,
        ];
    }

    /**
     * @return LengthAwarePaginator<int, LoaPreenchimento>
     */
    private function linhasPaginadas(): LengthAwarePaginator
    {
        return LoaPreenchimento::query()
            ->where('orcamento_id', $this->orcamentoId)
            ->when($this->filtroUnidadeId, fn ($q) => $q->where('unidade_id', $this->filtroUnidadeId))
            ->with(['unidade', 'subunidade', 'loaAcao', 'fonte', 'natureza', 'corte.admin'])
            ->orderBy('unidade_id')
            ->orderBy('subunidade_id')
            ->orderBy('id')
            ->paginate(25);
    }

    public function render()
    {
        $orcamento = Orcamento::find($this->orcamentoId);

        $linhas = $orcamento ? $this->linhasPaginadas() : null;

        $classificacaoRows = $orcamento ? $this->resumoPorClassificacao() : collect();
        $unidadeRows = $orcamento ? $this->resumoPorUnidade() : collect();
        $totais = $orcamento ? $this->totaisGlobais() : ['liberado_centavos' => 0, 'preenchido_centavos' => 0, 'diferenca_centavos' => 0];

        $unidadesFiltro = Unidade::query()->orderBy('descricao')->get();

        $cortesRegistrados = $orcamento
            ? Corte::query()
                ->where('orcamento_id', $this->orcamentoId)
                ->with(['loaPreenchimento.unidade', 'admin'])
                ->latest('updated_at')
                ->limit(50)
                ->get()
            : collect();

        return view('livewire.admin.cortes-orcamento', [
            'orcamento' => $orcamento,
            'linhas' => $linhas,
            'classificacaoRows' => $classificacaoRows,
            'unidadeRows' => $unidadeRows,
            'totais' => $totais,
            'unidadesFiltro' => $unidadesFiltro,
            'cortesRegistrados' => $cortesRegistrados,
        ]);
    }
}
