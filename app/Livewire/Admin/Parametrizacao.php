<?php

namespace App\Livewire\Admin;

use App\Enums\OrcamentoStatus;
use App\Models\FonteRecurso;
use App\Models\FonteUnidadeRestricao;
use App\Models\Orcamento;
use App\Models\OrcamentoPrazo;
use App\Models\ParametrizacaoSecretaria;
use App\Models\Receita;
use App\Models\RegraFonte;
use App\Models\Subunidade;
use App\Models\Unidade;
use App\Services\FonteVisibilidadeService;
use App\Services\ReceitaDisponivelService;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Parametrizacao extends Component
{
    public int $orcamentoId;

    public string $activeTab = 'fontes';

    // Regras Fonte
    public string $fonteOrigem = '';

    public string $fonteDestino = '';

    // Restricao Fonte
    public string $restricaoFonteInicio = '';

    public string $restricaoFonteFim = '';

    public ?int $restricaoUnidadeId = null;

    public ?int $restricaoSubunidadeId = null;

    // Parametrizacao Secretaria
    public ?int $paramUnidadeId = null;

    public ?int $paramSubunidadeId = null;

    public ?int $paramFonteId = null;

    public string $paramClassificacao = 'custeio';

    public ?float $paramPercentualAnterior = null;

    public int $paramValorLiberado = 0;

    // Prazos
    public ?int $prazoUnidadeId = null;

    public string $prazoEstendido = '';

    public function mount(int $orcamentoId): void
    {
        $this->orcamentoId = $orcamentoId;
        $this->sincronizarFontesDisponiveis();
    }

    public function updatedRestricaoUnidadeId(): void
    {
        $this->restricaoSubunidadeId = null;
    }

    public function updatedParamUnidadeId(): void
    {
        $this->paramSubunidadeId = null;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // --- Regras Fonte ---
    public function addRegraFonte(): void
    {
        $this->validate([
            'fonteOrigem' => 'required|string|max:10',
            'fonteDestino' => 'required|string|max:10',
        ]);

        FonteRecurso::firstOrCreate(
            ['codigo' => $this->fonteOrigem],
            ['descricao' => 'Fonte '.$this->fonteOrigem]
        );

        FonteRecurso::firstOrCreate(
            ['codigo' => $this->fonteDestino],
            ['descricao' => 'Fonte '.$this->fonteDestino]
        );

        RegraFonte::firstOrCreate(
            ['orcamento_id' => $this->orcamentoId, 'fonte_origem' => $this->fonteOrigem],
            ['fonte_destino' => $this->fonteDestino]
        );

        $this->fonteOrigem = '';
        $this->fonteDestino = '';
    }

    public function deleteRegraFonte(int $id): void
    {
        RegraFonte::where('id', $id)->where('orcamento_id', $this->orcamentoId)->delete();
    }

    // --- Restricao Fonte ---
    public function addRestricaoFonte(): void
    {
        $this->validate([
            'restricaoFonteInicio' => 'required|string|max:10',
            'restricaoFonteFim' => 'required|string|max:10',
            'restricaoUnidadeId' => 'required|exists:unidades,id',
            'restricaoSubunidadeId' => [
                'nullable',
                Rule::exists('subunidades', 'id')->where('unidade_id', $this->restricaoUnidadeId),
            ],
        ]);

        FonteUnidadeRestricao::create([
            'orcamento_id' => $this->orcamentoId,
            'fonte_recurso_inicio' => $this->restricaoFonteInicio,
            'fonte_recurso_fim' => $this->restricaoFonteFim,
            'unidade_id' => $this->restricaoUnidadeId,
            'subunidade_id' => $this->restricaoSubunidadeId,
        ]);

        $this->reset(['restricaoFonteInicio', 'restricaoFonteFim', 'restricaoUnidadeId', 'restricaoSubunidadeId']);
    }

    public function deleteRestricaoFonte(int $id): void
    {
        FonteUnidadeRestricao::where('id', $id)->where('orcamento_id', $this->orcamentoId)->delete();
    }

    // --- Parametrizacao Secretaria ---
    public function addParametrizacao(): void
    {
        $this->validate([
            'paramUnidadeId' => 'required|exists:unidades,id',
            'paramSubunidadeId' => [
                'nullable',
                Rule::exists('subunidades', 'id')->where('unidade_id', $this->paramUnidadeId),
            ],
            'paramFonteId' => 'required|exists:fontes_recurso,id',
            'paramClassificacao' => 'required|in:geral,custeio,pessoal,investimento,terceirizacao',
            'paramPercentualAnterior' => 'nullable|numeric|min:0|max:999.99',
            'paramValorLiberado' => 'required|integer|min:0',
        ]);

        $fonte = FonteRecurso::findOrFail($this->paramFonteId);
        $svc = app(ReceitaDisponivelService::class);
        $tetoReceita = $svc->totalReceitaCentavosPorCodigoFonte($this->orcamentoId, $fonte->codigo);
        $jaLiberado = $svc->totalLiberadoCentavosPorFonteId($this->orcamentoId, $fonte->id);

        if ($jaLiberado + $this->paramValorLiberado > $tetoReceita) {
            $this->addError(
                'paramValorLiberado',
                'A soma dos valores liberados para a fonte '.$fonte->codigo.' não pode ultrapassar a receita total importada para essa fonte (após regras de substituição). '
                .'Receita: R$ '.number_format($tetoReceita / 100, 2, ',', '.').'; '
                .'já liberado: R$ '.number_format($jaLiberado / 100, 2, ',', '.').'; '
                .'disponível: R$ '.number_format(max(0, $tetoReceita - $jaLiberado) / 100, 2, ',', '.').'.'
            );

            return;
        }

        ParametrizacaoSecretaria::create([
            'orcamento_id' => $this->orcamentoId,
            'unidade_id' => $this->paramUnidadeId,
            'subunidade_id' => $this->paramSubunidadeId,
            'fonte_id' => $this->paramFonteId,
            'classificacao' => $this->paramClassificacao,
            'percentual_anterior' => $this->paramPercentualAnterior,
            'valor_liberado' => $this->paramValorLiberado,
        ]);

        $this->reset(['paramUnidadeId', 'paramSubunidadeId', 'paramFonteId', 'paramPercentualAnterior', 'paramValorLiberado']);
        $this->paramClassificacao = 'custeio';
        $this->paramValorLiberado = 0;
    }

    public function deleteParametrizacao(int $id): void
    {
        ParametrizacaoSecretaria::where('id', $id)->where('orcamento_id', $this->orcamentoId)->delete();
    }

    // --- Prazos ---
    public function addPrazo(): void
    {
        $this->validate([
            'prazoUnidadeId' => 'required|exists:unidades,id',
            'prazoEstendido' => 'required|date|after:today',
        ]);

        OrcamentoPrazo::updateOrCreate(
            ['orcamento_id' => $this->orcamentoId, 'unidade_id' => $this->prazoUnidadeId],
            ['prazo_estendido' => $this->prazoEstendido]
        );

        $this->reset(['prazoUnidadeId', 'prazoEstendido']);
    }

    public function deletePrazo(int $id): void
    {
        OrcamentoPrazo::where('id', $id)->where('orcamento_id', $this->orcamentoId)->delete();
    }

    // --- Liberar ---
    public function liberarPreenchimento(): void
    {
        $orcamento = Orcamento::findOrFail($this->orcamentoId);
        $orcamento->update(['status' => OrcamentoStatus::Aberto]);
        session()->flash('message', 'Orçamento liberado para preenchimento!');
    }

    private function sincronizarFontesDisponiveis(): void
    {
        $codigosReceita = Receita::query()
            ->where('orcamento_id', $this->orcamentoId)
            ->pluck('fonte_recurso')
            ->filter()
            ->unique();

        foreach ($codigosReceita as $codigo) {
            FonteRecurso::firstOrCreate(
                ['codigo' => trim((string) $codigo)],
                ['descricao' => 'Fonte '.trim((string) $codigo)]
            );
        }

        $regras = RegraFonte::query()
            ->where('orcamento_id', $this->orcamentoId)
            ->get(['fonte_origem', 'fonte_destino']);

        foreach ($regras as $regra) {
            foreach ([$regra->fonte_origem, $regra->fonte_destino] as $codigo) {
                if (! $codigo) {
                    continue;
                }

                FonteRecurso::firstOrCreate(
                    ['codigo' => trim((string) $codigo)],
                    ['descricao' => 'Fonte '.trim((string) $codigo)]
                );
            }
        }
    }

    public function render()
    {
        $subunidadesRestricao = $this->restricaoUnidadeId
            ? Subunidade::where('unidade_id', $this->restricaoUnidadeId)->orderBy('codigo')->get()
            : collect();

        $subunidadesParam = $this->paramUnidadeId
            ? Subunidade::where('unidade_id', $this->paramUnidadeId)->orderBy('codigo')->get()
            : collect();

        $fontes = FonteRecurso::orderBy('codigo')->get();
        $unidadeParam = $this->paramUnidadeId ? Unidade::find($this->paramUnidadeId) : null;
        $fontesParametrizacao = app(FonteVisibilidadeService::class)->fontesPermitidasParaUnidade($fontes, $unidadeParam);
        $svc = app(ReceitaDisponivelService::class);
        $totReceitaCentavos = $svc->totalReceitaCentavosOrcamento($this->orcamentoId);
        $totLiberadoCentavos = $svc->totalLiberadoCentavosOrcamento($this->orcamentoId);
        $receitaPorFonte = [];
        foreach ($fontes as $f) {
            $rec = $svc->totalReceitaCentavosPorCodigoFonte($this->orcamentoId, $f->codigo);
            $lib = $svc->totalLiberadoCentavosPorFonteId($this->orcamentoId, $f->id);
            $receitaPorFonte[$f->id] = [
                'receita_centavos' => $rec,
                'liberado_centavos' => $lib,
                'saldo_centavos' => max(0, $rec - $lib),
            ];
        }

        return view('livewire.admin.parametrizacao', [
            'orcamento' => Orcamento::findOrFail($this->orcamentoId),
            'regrasFonte' => RegraFonte::where('orcamento_id', $this->orcamentoId)->get(),
            'restricoesFonte' => FonteUnidadeRestricao::where('orcamento_id', $this->orcamentoId)->with(['unidade', 'subunidade'])->get(),
            'parametrizacoes' => ParametrizacaoSecretaria::where('orcamento_id', $this->orcamentoId)->with(['unidade', 'subunidade', 'fonte'])->get(),
            'prazos' => OrcamentoPrazo::where('orcamento_id', $this->orcamentoId)->with('unidade')->get(),
            'unidades' => Unidade::orderBy('codigo')->get(),
            'fontes' => $fontes,
            'fontesParametrizacao' => $fontesParametrizacao,
            'subunidadesRestricao' => $subunidadesRestricao,
            'subunidadesParam' => $subunidadesParam,
            'totReceitaCentavos' => $totReceitaCentavos,
            'totLiberadoCentavos' => $totLiberadoCentavos,
            'receitaPorFonte' => $receitaPorFonte,
        ]);
    }
}
