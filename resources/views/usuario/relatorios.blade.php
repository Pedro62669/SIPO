<x-app-layout>
    @php
        $unidadeId = auth()->user()->unidade_id;

        $orcamentos = \App\Models\Orcamento::query()
            ->withCount([
                'loaPreenchimentos as meus_preenchimentos_count' => fn ($q) => $q->where('unidade_id', $unidadeId),
                'enviosOrcamento as meus_envios_count' => fn ($q) => $q->where('unidade_id', $unidadeId)->where('status', \App\Enums\EnvioOrcamentoStatus::Enviado),
            ])
            ->withSum([
                'loaPreenchimentos as meu_valor_loa' => fn ($q) => $q->where('unidade_id', $unidadeId),
            ], 'valor')
            ->latest('created_at')
            ->get();

        $totalOrcamentos = $orcamentos->count();
        $totalPreenchimentos = (int) $orcamentos->sum('meus_preenchimentos_count');
        $totalEnvios = (int) $orcamentos->sum('meus_envios_count');
        $valorTotalLoa = (int) $orcamentos->sum(fn ($orcamento) => (int) ($orcamento->meu_valor_loa ?? 0));
    @endphp

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Relatórios</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Orçamentos Disponíveis</p>
                    <p class="mt-1 text-2xl font-bold text-gray-900">{{ $totalOrcamentos }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Minhas Linhas LOA</p>
                    <p class="mt-1 text-2xl font-bold text-blue-700">{{ $totalPreenchimentos }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Meus Envios</p>
                    <p class="mt-1 text-2xl font-bold text-green-700">{{ $totalEnvios }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-lg p-5">
                    <p class="text-sm text-gray-500">Valor Total LOA</p>
                    <p class="mt-1 text-2xl font-bold text-purple-700">R$ {{ number_format($valorTotalLoa, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Meus Dados por Orçamento</h3>

                @if ($orcamentos->isEmpty())
                    <p class="text-sm text-gray-500">Nenhum orçamento disponível.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Orçamento</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Minhas Linhas LOA</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Valor LOA</th>
                                    <th class="px-4 py-3 text-right font-semibold text-gray-600">Envios</th>
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
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $orcamento->meus_preenchimentos_count }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">R$ {{ number_format((int) ($orcamento->meu_valor_loa ?? 0), 2, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">{{ $orcamento->meus_envios_count }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('usuario.loa.enviar', ['orcamento' => $orcamento->id]) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                                Consultar
                                            </a>
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
