<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class DespesaImport implements ToCollection
{
    public Collection $importedRows;

    public function __construct(
        private int $orcamentoId
    ) {
        $this->importedRows = collect();
    }

    public function collection(Collection $rows): void
    {
        $headerIdx = null;
        $hasDesdobrada = false;

        foreach ($rows as $idx => $row) {
            $vals = $row->map(fn ($v) => mb_strtolower(trim((string) ($v ?? ''))))->toArray();
            $hasAno = in_array('ano', $vals, true);
            $hasDespesa = in_array('despesa', $vals, true) || in_array('principal', $vals, true);

            if ($hasAno && $hasDespesa) {
                $headerIdx = $idx;
                $hasDesdobrada = in_array('desdobrada', $vals, true);
                break;
            }
        }

        if ($headerIdx === null) {
            return;
        }

        $offset = $hasDesdobrada ? 1 : 0;

        foreach ($rows as $idx => $row) {
            if ($idx <= $headerIdx) {
                continue;
            }
            $v = $row->toArray();

            $ano = $v[0] ?? null;
            if (! $ano || ! is_numeric($ano)) {
                continue;
            }

            $this->importedRows->push([
                'orcamento_id' => $this->orcamentoId,
                'ano' => (int) $ano,
                'numero_despesa' => (int) ($v[1] ?? 0),
                'natureza_codigo' => trim((string) ($v[2 + $offset] ?? '')),
                'fonte_codigo' => trim((string) ($v[3 + $offset] ?? '')),
                'unidade_codigo' => (int) ($v[4 + $offset] ?? 0),
                'unidade_descricao' => trim((string) ($v[5 + $offset] ?? '')),
                'subunidade_codigo' => (int) ($v[6 + $offset] ?? 0),
                'subunidade_descricao' => trim((string) ($v[7 + $offset] ?? '')),
                'programa_codigo' => (int) ($v[8 + $offset] ?? 0),
                'programa_descricao' => trim((string) ($v[9 + $offset] ?? '')),
                'acao_codigo' => (int) ($v[10 + $offset] ?? 0),
                'acao_descricao' => trim((string) ($v[11 + $offset] ?? '')),
            ]);
        }
    }
}
