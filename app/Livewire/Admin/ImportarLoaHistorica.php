<?php

namespace App\Livewire\Admin;

use App\Enums\OrcamentoStatus;
use App\Enums\OrcamentoTipo;
use App\Imports\DespesaImport;
use App\Imports\ParametrizacaoHistoricaImport;
use App\Imports\SaldoDespesaImport;
use App\Models\Orcamento;
use App\Services\ImportacaoService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class ImportarLoaHistorica extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public ?int $orcamento_id = null;

    // Etapa 1
    public int $ano;

    // Etapa 2 - Despesa
    public $despesaFile = null;

    public ?array $despesaResult = null;

    // Etapa 3 - Saldo
    public $saldoFile = null;

    public ?array $saldoResult = null;

    // Etapa 4 - Parametrização
    public $parametrizacaoFile = null;

    public ?array $parametrizacaoResult = null;

    public function mount(): void
    {
        $this->ano = (int) date('Y') - 1;
    }

    public function criarOrcamentoHistorico(): void
    {
        $this->validate([
            'ano' => 'required|integer|min:2000|max:2100',
        ]);

        $existente = Orcamento::where('ano', $this->ano)
            ->where('tipo', OrcamentoTipo::LOA)
            ->where('is_historico', true)
            ->first();

        if ($existente) {
            $this->addError('ano', "Já existe uma LOA histórica importada para o ano {$this->ano}.");

            return;
        }

        $orcamento = Orcamento::create([
            'ano' => $this->ano,
            'tipo' => OrcamentoTipo::LOA,
            'status' => OrcamentoStatus::Finalizado,
            'prazo_preenchimento' => null,
            'is_historico' => true,
            'created_by' => auth()->id(),
        ]);

        $this->orcamento_id = $orcamento->id;
        $this->step = 2;
    }

    public function updatedDespesaFile(): void
    {
        $this->importarDespesa();
    }

    public function updatedSaldoFile(): void
    {
        $this->importarSaldo();
    }

    public function updatedParametrizacaoFile(): void
    {
        $this->importarParametrizacao();
    }

    public function importarDespesa(): void
    {
        if (! $this->orcamento_id || ! $this->despesaFile) {
            return;
        }

        $this->validate([
            'despesaFile' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $import = new DespesaImport($this->orcamento_id);
        Excel::import($import, $this->despesaFile->getRealPath());

        $result = app(ImportacaoService::class)->processarDespesas(
            $this->orcamento_id,
            $import->importedRows,
            aplicarRegrasFonte: false
        );

        $this->despesaResult = $result;
        $this->despesaFile = null;
        $this->step = 3;
    }

    public function importarSaldo(): void
    {
        if (! $this->orcamento_id || ! $this->saldoFile) {
            return;
        }

        $this->validate([
            'saldoFile' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $import = new SaldoDespesaImport;
        Excel::import($import, $this->saldoFile->getRealPath());

        $result = app(ImportacaoService::class)->processarSaldos(
            $this->orcamento_id,
            $import->importedRows
        );

        $this->saldoResult = $result;
        $this->saldoFile = null;
        $this->step = 4;
    }

    public function pularSaldo(): void
    {
        $this->step = 4;
    }

    public function importarParametrizacao(): void
    {
        if (! $this->orcamento_id || ! $this->parametrizacaoFile) {
            return;
        }

        $this->validate([
            'parametrizacaoFile' => 'required|file|mimes:xlsx,xls,csv|max:20480',
        ]);

        $import = new ParametrizacaoHistoricaImport;
        Excel::import($import, $this->parametrizacaoFile->getRealPath());

        $result = app(ImportacaoService::class)->processarParametrizacoesHistoricas(
            $this->orcamento_id,
            $import->importedRows
        );

        $this->parametrizacaoResult = $result;
        $this->parametrizacaoFile = null;
        $this->step = 5;
    }

    public function pularParametrizacao(): void
    {
        $this->step = 5;
    }

    public function voltarStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function render()
    {
        $orcamento = $this->orcamento_id ? Orcamento::find($this->orcamento_id) : null;

        return view('livewire.admin.importar-loa-historica', [
            'orcamento' => $orcamento,
        ]);
    }
}
