<x-app-layout>
    @php
        $orcamentos = \App\Models\Orcamento::query()
            ->withCount(['receitas', 'despesasImportadas', 'loaPreenchimentos'])
            ->withSum('receitas', 'valor')
            ->withSum('loaPreenchimentos', 'valor')
            ->withSum('despesasImportadas', 'valor_inicial')
            ->withSum('despesasImportadas', 'dotacao_atualizada')
            ->latest('created_at')
            ->get()
            ->map(function ($orcamento) {
                $valorHistorico = (int) (
                    $orcamento->despesas_importadas_sum_dotacao_atualizada
                    ?: $orcamento->despesas_importadas_sum_valor_inicial
                    ?: 0
                );

                $orcamento->valor_loa_relatorio = $orcamento->is_historico
                    ? $valorHistorico
                    : (int) ($orcamento->loa_preenchimentos_sum_valor ?? 0);

                return $orcamento;
            });

        $totalOrcamentos = $orcamentos->count();
        $totalReceitas = (int) $orcamentos->sum(fn ($orcamento) => (int) ($orcamento->receitas_sum_valor ?? 0));
        $totalPreenchido = (int) $orcamentos->sum(fn ($orcamento) => (int) ($orcamento->loa_preenchimentos_sum_valor ?? 0));
        $totalDespesasImportadas = (int) $orcamentos->sum('despesas_importadas_count');
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Relatórios</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800 border border-green-200">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Orçamentos</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $totalOrcamentos }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Receita Total Importada</p>
                    <p class="mt-1 text-2xl font-bold text-blue-700">R$ {{ number_format($totalReceitas / 100, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">LOA Preenchida</p>
                    <p class="mt-1 text-2xl font-bold text-green-700">R$ {{ number_format($totalPreenchido, 2, ',', '.') }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Despesas Importadas</p>
                    <p class="mt-1 text-2xl font-bold text-purple-700">{{ $totalDespesasImportadas }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Consolidação por Orçamento</h3>

                @if ($orcamentos->isEmpty())
                    <p class="text-sm text-gray-500">Nenhum orçamento encontrado para relatório.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Orçamento</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Receitas</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Despesas Importadas</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-600">LOA Preenchimentos</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Valor LOA</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @foreach ($orcamentos as $orcamento)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-900 font-medium">{{ $orcamento->tipo->value }} {{ $orcamento->ano }}</td>
                                        <td class="px-4 py-3">
                                            @if ($orcamento->status === \App\Enums\OrcamentoStatus::Aberto)
                                                <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Aberto</span>
                                            @else
                                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">Finalizado</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $orcamento->receitas_count }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $orcamento->despesas_importadas_count }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $orcamento->loa_preenchimentos_count }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">
                                            R$ {{ number_format($orcamento->is_historico ? $orcamento->valor_loa_relatorio / 100 : $orcamento->valor_loa_relatorio, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <div class="inline-flex flex-wrap items-center justify-end gap-x-3 gap-y-1">
                                                <a href="{{ $orcamento->is_historico ? route('admin.loa-historica.consultar') : route('admin.orcamento.cortes', ['orcamento' => $orcamento->id]) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                    Consultar
                                                </a>
                                                <livewire:admin.botao-excluir-orcamento
                                                    :key="'excluir-relatorio-'.$orcamento->id"
                                                    :orcamento-id="$orcamento->id"
                                                    :tipo="$orcamento->tipo->value"
                                                    :ano="$orcamento->ano"
                                                    apos-excluir-rota="admin.relatorios"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
