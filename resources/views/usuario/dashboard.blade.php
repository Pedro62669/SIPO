<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Elaboração da LOA - {{ auth()->user()->unidade?->descricao ?? 'Sem unidade' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @php
                $orcamentoAberto = \App\Models\Orcamento::where('status', 'aberto')->latest()->first();
            @endphp

            @if($orcamentoAberto)
                @php
                    $totalLiberadoCentavos = (int) \App\Models\ParametrizacaoSecretaria::where('orcamento_id', $orcamentoAberto->id)
                        ->where('unidade_id', auth()->user()->unidade_id)->sum('valor_liberado');
                    $totalPreenchidoReais = (int) \App\Models\LoaPreenchimento::where('orcamento_id', $orcamentoAberto->id)
                        ->where('unidade_id', auth()->user()->unidade_id)->sum('valor');
                    $totalPreenchidoCentavos = $totalPreenchidoReais * 100;
                    $percentual = $totalLiberadoCentavos > 0 ? round(($totalPreenchidoCentavos / $totalLiberadoCentavos) * 100) : 0;
                @endphp
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">
                                    {{ $orcamentoAberto->tipo->value }} {{ $orcamentoAberto->ano }} -
                                    <span class="text-green-600">Aberto</span>
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Prazo para elaboração: {{ $orcamentoAberto->prazo_preenchimento?->format('d/m/Y') ?? 'Não definido' }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Total liberado</p>
                                <p class="text-2xl font-bold text-gray-900">R$ {{ number_format($totalLiberadoCentavos / 100, 2, ',', '.') }}</p>
                                <p class="text-sm {{ $percentual > 100 ? 'text-red-500' : 'text-green-500' }}">
                                    {{ $percentual }}% executado (R$ {{ number_format($totalPreenchidoCentavos / 100, 2, ',', '.') }})
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="{{ route('usuario.loa.preencher', $orcamentoAberto) }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow cursor-pointer">
                        <div class="p-6 text-center">
                            <svg class="h-12 w-12 text-blue-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <h4 class="font-medium text-gray-900">Preencher LOA</h4>
                            <p class="text-xs text-gray-500 mt-1">Lançar valores por ação</p>
                        </div>
                    </a>

                    <a href="{{ route('usuario.loa.metas-acoes', $orcamentoAberto) }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow cursor-pointer">
                        <div class="p-6 text-center">
                            <svg class="h-12 w-12 text-green-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <h4 class="font-medium text-gray-900">Metas e Ações</h4>
                            <p class="text-xs text-gray-500 mt-1">Gerenciar ações da LOA</p>
                        </div>
                    </a>

                    <a href="{{ route('usuario.loa.obras', $orcamentoAberto) }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow cursor-pointer">
                        <div class="p-6 text-center">
                            <svg class="h-12 w-12 text-orange-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <h4 class="font-medium text-gray-900">Obras</h4>
                            <p class="text-xs text-gray-500 mt-1">Ações e valores de obras</p>
                        </div>
                    </a>

                    <a href="{{ route('usuario.loa.enviar', $orcamentoAberto) }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow cursor-pointer">
                        <div class="p-6 text-center">
                            <svg class="h-12 w-12 text-purple-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <h4 class="font-medium text-gray-900">Enviar Orçamento</h4>
                            <p class="text-xs text-gray-500 mt-1">Submeter para aprovação</p>
                        </div>
                    </a>

                    <a href="{{ route('usuario.loa-historica') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow cursor-pointer">
                        <div class="p-6 text-center">
                            <svg class="h-12 w-12 text-indigo-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h4 class="font-medium text-gray-900">LOAs Anteriores</h4>
                            <p class="text-xs text-gray-500 mt-1">Consultar histórico</p>
                        </div>
                    </a>
                </div>
            @else
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <p class="text-yellow-700">Nenhum orçamento aberto no momento. Aguarde a liberação pela Secretaria de Planejamento.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <a href="{{ route('usuario.loa-historica') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow cursor-pointer">
                        <div class="p-6 text-center">
                            <svg class="h-12 w-12 text-indigo-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h4 class="font-medium text-gray-900">LOAs Anteriores</h4>
                            <p class="text-xs text-gray-500 mt-1">Consultar histórico</p>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
