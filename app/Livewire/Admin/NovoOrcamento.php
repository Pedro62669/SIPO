<?php

namespace App\Livewire\Admin;

use App\Enums\OrcamentoStatus;
use App\Enums\OrcamentoTipo;
use App\Imports\DespesaImport;
use App\Imports\ReceitaImport;
use App\Models\Orcamento;
use App\Models\Receita;
use App\Services\ImportacaoService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class NovoOrcamento extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public ?int $orcamento_id = null;

    // Step 1 - Criar
    public int $ano;

    public string $tipo = 'LOA';

    public string $prazo_preenchimento = '';

    public ?int $periodo_ppa_inicio = null;

    public ?int $periodo_ppa_fim = null;

    // Step 2 - Receita
    public $receitaFile = null;

    public ?float $percentualProjecao = null;

    public ?array $receitaResult = null;

    // Step 3 - Despesa
    public $despesaFile = null;

    public ?array $despesaResult = null;

    public function mount(): void
    {
        $this->ano = (int) date('Y') + 1;
    }

    public function criarOrcamento(): void
    {
        $rules = [
            'ano' => 'required|integer|min:2024|max:2050',
            'tipo' => 'required|in:LOA,PPA',
            'prazo_preenchimento' => 'required|date|after:today',
        ];

        if ($this->tipo === 'PPA') {
            $rules['periodo_ppa_inicio'] = 'required|integer|min:2020|max:2100';
            $rules['periodo_ppa_fim'] = 'required|integer|gte:periodo_ppa_inicio|max:2100';
        }

        $this->validate($rules);

        $orcamento = Orcamento::create([
            'ano' => $this->ano,
            'tipo' => OrcamentoTipo::from($this->tipo),
            'status' => OrcamentoStatus::Aberto,
            'prazo_preenchimento' => $this->prazo_preenchimento,
            'periodo_ppa_inicio' => $this->tipo === 'PPA' ? $this->periodo_ppa_inicio : null,
            'periodo_ppa_fim' => $this->tipo === 'PPA' ? $this->periodo_ppa_fim : null,
            'created_by' => auth()->id(),
        ]);

        $this->orcamento_id = $orcamento->id;

        app(ImportacaoService::class)->criarRegrasSubstituicaoFontePadrao($orcamento->id);

        $this->step = 2;
    }

    public function importarReceita(): void
    {
        if (! $this->orcamento_id) {
            return;
        }

        $this->validate([
            'receitaFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $import = new ReceitaImport($this->orcamento_id);
        Excel::import($import, $this->receitaFile->getRealPath());

        $count = Receita::where('orcamento_id', $this->orcamento_id)->count();

        if ($this->percentualProjecao && $this->percentualProjecao != 0) {
            $receitas = Receita::where('orcamento_id', $this->orcamento_id)->get();
            foreach ($receitas as $receita) {
                $projetado = (int) round($receita->valor * (1 + $this->percentualProjecao / 100));
                $receita->update([
                    'percentual_projecao' => $this->percentualProjecao,
                    'valor_projetado' => $projetado,
                ]);
            }
        }

        $totalValor = Receita::where('orcamento_id', $this->orcamento_id)->sum('valor');

        $this->receitaResult = [
            'count' => $count,
            'total' => $totalValor,
        ];

        $this->receitaFile = null;
        $this->step = 3;
    }

    public function pularReceita(): void
    {
        $this->step = 3;
    }

    public function voltarStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function importarDespesa(): void
    {
        if (! $this->orcamento_id) {
            return;
        }

        $this->validate([
            'despesaFile' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $import = new DespesaImport($this->orcamento_id);
        Excel::import($import, $this->despesaFile->getRealPath());

        $result = app(ImportacaoService::class)->processarDespesas(
            $this->orcamento_id,
            $import->importedRows
        );

        $this->despesaResult = $result;
        $this->despesaFile = null;
        $this->step = 4;
    }

    public function finalizar(): void
    {
        if (! $this->orcamento_id) {
            $this->redirectRoute('admin.dashboard', navigate: true);
            return;
        }

        $this->redirectRoute('admin.orcamento.parametrizacao', ['orcamento' => $this->orcamento_id], navigate: true);
    }

    public function render()
    {
        $orcamento = $this->orcamento_id ? Orcamento::find($this->orcamento_id) : null;

        return view('livewire.admin.novo-orcamento', [
            'orcamento' => $orcamento,
        ]);
    }
}
