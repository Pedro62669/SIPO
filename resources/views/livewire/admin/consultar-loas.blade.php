<div>
    @if (session('status'))
        <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">LOA / Ano</label>
                <div class="flex gap-2 items-center">
                    <select wire:model.live="orcamento_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">— selecione —</option>
                        @foreach ($orcamentosHistoricos as $orc)
                            <option value="{{ $orc->id }}">LOA {{ $orc->ano }}</option>
                        @endforeach
                    </select>
                    @if ($orcamento_id)
                        <button type="button"
                            wire:click="excluir({{ $orcamento_id }})"
                            wire:confirm="Excluir definitivamente a LOA {{ $orcamentosHistoricos->firstWhere('id', $orcamento_id)?->ano }}? Todos os dados importados serão removidos."
                            class="flex-shrink-0 text-red-500 hover:text-red-700 transition"
                            title="Excluir esta LOA">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Secretaria</label>
                <select wire:model.live="unidade_id"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    {{ ! $orcamento_id ? 'disabled' : '' }}>
                    <option value="">Todas as secretarias</option>
                    @foreach ($unidades as $u)
                        <option value="{{ $u->id }}">{{ $u->codigo }} – {{ $u->descricao }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                <input wire:model.live.debounce.400ms="busca" type="text"
                    placeholder="Ação, natureza, programa..."
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    {{ ! $orcamento_id ? 'disabled' : '' }}>
            </div>
        </div>
    </div>

    @if (! $orcamento_id)
        <div class="text-center py-16 text-gray-400">
            <svg class="mx-auto h-12 w-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm">Selecione uma LOA para visualizar os dados.</p>
            @if ($orcamentosHistoricos->isEmpty())
                <p class="text-xs mt-2">Nenhuma LOA histórica importada ainda.
                    <a href="{{ route('admin.loa-historica.importar') }}" class="text-indigo-600 hover:underline">Importar agora</a>
                </p>
            @endif
        </div>
    @else
        {{-- Totalizadores --}}
        @if ($totais)
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Dotação Inicial</p>
                    <p class="text-lg font-bold text-gray-800">R$ {{ number_format(($totais->total_inicial ?? 0) / 100, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Dotação Atualizada</p>
                    <p class="text-lg font-bold text-indigo-700">R$ {{ number_format(($totais->total_dotacao_atualizada ?? 0) / 100, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Empenhado</p>
                    <p class="text-lg font-bold text-blue-700">R$ {{ number_format(($totais->total_empenhado ?? 0) / 100, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Liquidado</p>
                    <p class="text-lg font-bold text-green-700">R$ {{ number_format(($totais->total_liquidado ?? 0) / 100, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Pago</p>
                    <p class="text-lg font-bold text-teal-700">R$ {{ number_format(($totais->total_pago ?? 0) / 100, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Saldo da Dotação</p>
                    @php $saldoDot = ($totais->total_saldo_dotacao ?? 0); @endphp
                    <p class="text-lg font-bold {{ $saldoDot >= 0 ? 'text-gray-800' : 'text-red-600' }}">
                        R$ {{ number_format($saldoDot / 100, 2, ',', '.') }}
                    </p>
                </div>
            </div>
        @endif

        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-gray-900">Parametrização importada</h3>
                <span class="text-xs text-gray-500">{{ $parametrizacoes->count() }} registros</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Total liberado</p>
                    <p class="text-lg font-bold text-blue-700">R$ {{ number_format(($paramTotais['total_liberado'] ?? 0) / 100, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <p class="text-xs text-gray-500 uppercase">Saldo vs. Empenhado</p>
                    @php $saldoVsEmpenhado = $paramTotais['saldo_vs_empenhado'] ?? null; @endphp
                    <p class="text-lg font-bold {{ $saldoVsEmpenhado >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                        R$ {{ number_format($saldoVsEmpenhado / 100, 2, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Unidade</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Subunidade</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Fonte</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-600">Classificação</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-600">% ant.</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-600">Valor liberado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse ($parametrizacoes as $p)
                            <tr>
                                <td class="px-4 py-2">{{ $p->unidade?->codigo }} — {{ $p->unidade?->descricao }}</td>
                                <td class="px-4 py-2">{{ $p->subunidade ? $p->subunidade->codigo.' — '.$p->subunidade->descricao : '—' }}</td>
                                <td class="px-4 py-2">{{ $p->fonte?->codigo }} — {{ $p->fonte?->descricao }}</td>
                                <td class="px-4 py-2">
                                    @switch($p->classificacao->value)
                                        @case('geral') Geral @break
                                        @case('terceirizacao') Terceirização @break
                                        @case('custeio') Custeio @break
                                        @case('pessoal') Pessoal @break
                                        @case('investimento') Investimento @break
                                        @default {{ $p->classificacao->value }}
                                    @endswitch
                                </td>
                                <td class="px-4 py-2 text-right">{{ $p->percentual_anterior !== null ? number_format((float) $p->percentual_anterior, 2, ',', '.') : '—' }}</td>
                                <td class="px-4 py-2 text-right font-medium">R$ {{ number_format($p->valor_liberado / 100, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-500">Nenhuma parametrização histórica importada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tabela --}}
        <div class="rounded-lg border border-gray-200 overflow-x-auto">
            <style>
                .loa-table tr.row-selected td { background-color: #bfdbfe !important; }
            </style>
            <table class="min-w-full divide-y divide-gray-200 text-xs whitespace-nowrap loa-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Secretaria</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Subunidade</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Programa</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Ação</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Natureza</th>
                        <th class="px-3 py-2 text-left font-medium text-gray-500 uppercase">Fonte</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Dot. Inicial</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Créditos Adicionais</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Redução Créditos</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Dot. Atualizada</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Empenhado</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Liquidado</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Pago</th>
                        <th class="px-3 py-2 text-right font-medium text-gray-500 uppercase">Saldo Dotação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($despesas as $d)
                        @php
                            $saldoLinha = $d->saldo_dotacao ?: ($d->valor_inicial - $d->empenhado);
                            $creditosAdicionais = $d->total_creditos_adicionais ?: ($d->credito_suplementar + $d->credito_especial);
                        @endphp
                        <tr class="{{ $loop->even ? 'bg-gray-200' : 'bg-white' }} hover:bg-blue-100 cursor-pointer" onclick="this.classList.toggle('row-selected')">
                            <td class="px-3 py-1.5">
                                <span class="font-mono text-gray-400">{{ $d->unidade?->codigo }}</span>
                                {{ $d->unidade?->descricao }}
                            </td>
                            <td class="px-3 py-1.5 text-gray-600">
                                <span class="font-mono text-gray-400">{{ $d->subunidade?->codigo }}</span>
                                {{ $d->subunidade?->descricao }}
                            </td>
                            <td class="px-3 py-1.5 text-gray-600">{{ $d->programa?->descricao }}</td>
                            <td class="px-3 py-1.5 text-gray-600">
                                <span class="font-mono text-gray-400">{{ $d->acao?->codigo }}</span>
                                {{ $d->acao?->descricao }}
                            </td>
                            <td class="px-3 py-1.5 font-mono text-gray-600">{{ $d->natureza?->codigo }}</td>
                            <td class="px-3 py-1.5 font-mono text-gray-600">{{ $d->fonte?->codigo }}</td>
                            <td class="px-3 py-1.5 text-right text-gray-700">{{ number_format($d->valor_inicial / 100, 2, ',', '.') }}</td>
                            <td class="px-3 py-1.5 text-right text-purple-700">{{ number_format($creditosAdicionais / 100, 2, ',', '.') }}</td>
                            <td class="px-3 py-1.5 text-right text-orange-700">{{ number_format($d->reducao_creditos / 100, 2, ',', '.') }}</td>
                            <td class="px-3 py-1.5 text-right text-indigo-700">{{ number_format($d->dotacao_atualizada / 100, 2, ',', '.') }}</td>
                            <td class="px-3 py-1.5 text-right text-blue-700">{{ number_format($d->empenhado / 100, 2, ',', '.') }}</td>
                            <td class="px-3 py-1.5 text-right text-green-700">{{ number_format($d->liquidado / 100, 2, ',', '.') }}</td>
                            <td class="px-3 py-1.5 text-right text-teal-700">{{ number_format($d->pago / 100, 2, ',', '.') }}</td>
                            <td class="px-3 py-1.5 text-right font-medium {{ $saldoLinha >= 0 ? 'text-gray-700' : 'text-red-600' }}">
                                {{ number_format($saldoLinha / 100, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="px-3 py-8 text-center text-gray-400 text-sm">Nenhum registro encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $despesas->links() }}
        </div>
    @endif
</div>
