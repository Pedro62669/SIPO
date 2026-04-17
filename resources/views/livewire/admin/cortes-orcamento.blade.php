@php
    use App\Enums\ParametrizacaoClassificacao;

    $classificacaoLabel = function (string $key) {
        $enum = ParametrizacaoClassificacao::tryFrom($key);

        return match ($enum) {
            ParametrizacaoClassificacao::Geral => 'Geral',
            ParametrizacaoClassificacao::Custeio => 'Custeio',
            ParametrizacaoClassificacao::Pessoal => 'Pessoal',
            ParametrizacaoClassificacao::Investimento => 'Investimento',
            ParametrizacaoClassificacao::Terceirizacao => 'Terceirização',
            default => $key === 'outros' ? 'Outros / sem vínculo' : ucfirst($key),
        };
    };
@endphp

<div>
    @if (!$orcamento)
        <div class="bg-white shadow-sm sm:rounded-lg p-6">
            <p class="text-gray-600">Orçamento não encontrado.</p>
        </div>
    @else
        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800 border border-green-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Cortes no LOA</h3>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $orcamento->tipo->value }} {{ $orcamento->ano }} · Compare liberado vs preenchido e reduza valores por linha do LOA.
                </p>
            </div>
            <a href="{{ route('admin.orcamento.parametrizacao', ['orcamento' => $orcamento->id]) }}" wire:navigate
                class="text-sm font-medium text-blue-600 hover:text-blue-800 shrink-0">
                Parametrização deste orçamento →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="rounded-lg bg-gray-50 border border-gray-100 p-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total liberado</p>
                <p class="text-xl font-bold text-gray-900 mt-1">
                    R$ {{ number_format($totais['liberado_centavos'] / 100, 2, ',', '.') }}
                </p>
            </div>
            <div class="rounded-lg bg-blue-50 border border-blue-100 p-4">
                <p class="text-xs text-blue-700 uppercase tracking-wide">Total preenchido</p>
                <p class="text-xl font-bold text-blue-900 mt-1">
                    R$ {{ number_format($totais['preenchido_centavos'] / 100, 2, ',', '.') }}
                </p>
            </div>
            <div class="rounded-lg border p-4 @if ($totais['diferenca_centavos'] < 0) bg-red-50 border-red-100 @else bg-emerald-50 border-emerald-100 @endif">
                <p class="text-xs uppercase tracking-wide @if ($totais['diferenca_centavos'] < 0) text-red-700 @else text-emerald-700 @endif">
                    Saldo (liberado − preenchido)
                </p>
                <p class="text-xl font-bold mt-1 @if ($totais['diferenca_centavos'] < 0) text-red-800 @else text-emerald-900 @endif">
                    R$ {{ number_format($totais['diferenca_centavos'] / 100, 2, ',', '.') }}
                </p>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="font-medium text-gray-900">Por classificação</h4>
                <p class="text-xs text-gray-500 mt-1">Mesma base do envio: parametrização em centavos e LOA em reais inteiros (×100).</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Classificação</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Liberado</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Preenchido</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Diferença</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($classificacaoRows as $row)
                            <tr wire:key="cls-{{ $row['classificacao'] }}">
                                <td class="px-4 py-3 text-gray-900">{{ $classificacaoLabel($row['classificacao']) }}</td>
                                <td class="px-4 py-3 text-right">R$ {{ number_format($row['liberado_centavos'] / 100, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">R$ {{ number_format($row['preenchido_centavos'] / 100, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-medium @if ($row['diferenca_centavos'] < 0) text-red-600 @elseif ($row['diferenca_centavos'] > 0) text-amber-700 @else text-gray-600 @endif">
                                    R$ {{ number_format($row['diferenca_centavos'] / 100, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">Sem dados de parametrização ou LOA.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="font-medium text-gray-900">Por secretaria (unidade)</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Unidade</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Liberado</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Preenchido</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Diferença</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($unidadeRows as $urow)
                            <tr wire:key="u-{{ $urow['unidade']->id }}">
                                <td class="px-4 py-3 text-gray-900">{{ $urow['unidade']->descricao }}</td>
                                <td class="px-4 py-3 text-right">R$ {{ number_format($urow['liberado_centavos'] / 100, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">R$ {{ number_format($urow['preenchido_centavos'] / 100, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-medium @if ($urow['diferenca_centavos'] < 0) text-red-600 @elseif ($urow['diferenca_centavos'] > 0) text-amber-700 @else text-gray-600 @endif">
                                    R$ {{ number_format($urow['diferenca_centavos'] / 100, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">Nenhuma unidade com parametrização ou LOA.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-end gap-4">
                <div class="max-w-md">
                    <label for="filtro-unidade-cortes" class="block text-sm font-medium text-gray-700 mb-1">Filtrar linhas por unidade</label>
                    <select id="filtro-unidade-cortes" wire:model.live="filtroUnidadeId"
                        class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Todas</option>
                        @foreach ($unidadesFiltro as $u)
                            <option value="{{ $u->id }}">{{ $u->descricao }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="font-medium text-gray-900">Linhas do LOA</h4>
                <p class="text-xs text-gray-500 mt-1">Valores em reais inteiros (como no preenchimento). Aplique corte para reduzir o valor da linha.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">ID</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Unidade</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Subunidade</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Ação LOA</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-600">Fonte</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Valor atual</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Corte</th>
                            <th class="px-4 py-3 text-right font-medium text-gray-600">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($linhas as $line)
                            <tr wire:key="loa-line-{{ $line->id }}">
                                <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $line->id }}</td>
                                <td class="px-4 py-3 text-gray-800">{{ $line->unidade?->descricao ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $line->subunidade?->codigo ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-800 max-w-xs truncate" title="{{ $line->loaAcao?->nome }}">
                                    {{ \Illuminate\Support\Str::limit($line->loaAcao?->nome ?? '—', 40) }}
                                </td>
                                <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $line->fonte?->codigo ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">
                                    R$ {{ number_format($line->valor, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-xs text-gray-600">
                                    @if ($line->corte)
                                        <span class="text-amber-700">−R$ {{ number_format($line->corte->valor_original - $line->corte->valor_cortado, 0, ',', '.') }}</span>
                                        <span class="block text-gray-400 mt-0.5">{{ $line->corte->updated_at?->format('d/m/Y H:i') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    @if ((int) $line->valor > 0)
                                        <button type="button" wire:click="openCorteModal({{ $line->id }})"
                                            class="text-indigo-600 hover:text-indigo-800 font-medium text-xs">
                                            Corte
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">Nenhuma linha de LOA neste orçamento.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($linhas->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $linhas->links() }}
                </div>
            @endif
        </div>

        @if ($cortesRegistrados->isNotEmpty())
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h4 class="font-medium text-gray-900">Registros de corte (últimos)</h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Linha</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Unidade</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">De</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Para</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Admin</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Atualizado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($cortesRegistrados as $corte)
                                <tr wire:key="corte-{{ $corte->id }}">
                                    <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ $corte->loa_preenchimento_id }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $corte->loaPreenchimento?->unidade?->descricao ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right">R$ {{ number_format($corte->valor_original, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-medium">R$ {{ number_format($corte->valor_cortado, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $corte->admin?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $corte->updated_at?->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    @if ($showCorteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500/75" wire:click="closeCorteModal"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Aplicar corte na linha</h4>
                    <form wire:submit="aplicarCorte" class="space-y-4">
                        <div>
                            <p class="text-sm text-gray-600">Valor atual da linha (reais inteiros)</p>
                            <p class="text-lg font-semibold text-gray-900">R$ {{ number_format($corteValorAtual, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <label for="corte-novo-valor" class="block text-sm font-medium text-gray-700">Novo valor após corte</label>
                            <input id="corte-novo-valor" type="number" min="0" step="1" wire:model="corteNovoValor"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @error('corteNovoValor')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="corte-justificativa" class="block text-sm font-medium text-gray-700">Justificativa (opcional)</label>
                            <textarea id="corte-justificativa" wire:model="corteJustificativa" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                            @error('corteJustificativa')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" wire:click="closeCorteModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">
                                Confirmar corte
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
