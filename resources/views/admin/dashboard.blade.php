<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Painel Administrativo - SIPO
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Card: Orçamentos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900">Orçamentos</h3>
                                <p class="text-sm text-gray-500">Gerenciar LOA e PPA</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.orcamento.novo') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Novo Orçamento &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card: Usuários -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900">Usuários</h3>
                                <p class="text-sm text-gray-500">Gerenciar secretarias</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.usuarios') }}" class="text-green-600 hover:text-green-800 text-sm font-medium">
                                Criar Usuário &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card: Relatórios -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900">Relatórios</h3>
                                <p class="text-sm text-gray-500">Acompanhamento e análises</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.relatorios') }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                Abrir Relatórios &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Card: LOAs Anteriores -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-5">
                                <h3 class="text-lg font-medium text-gray-900">LOAs Anteriores</h3>
                                <p class="text-sm text-gray-500">Histórico e consulta</p>
                            </div>
                        </div>
                        <div class="mt-4 flex gap-4">
                            <a href="{{ route('admin.loa-historica.consultar') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                                Consultar &rarr;
                            </a>
                            <a href="{{ route('admin.loa-historica.importar') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
                                Importar
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumo estatístico -->
            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Resumo do Sistema</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center">
                            <span class="text-3xl font-bold text-blue-600">{{ \App\Models\Unidade::count() }}</span>
                            <p class="text-sm text-gray-500 mt-1">Unidades</p>
                        </div>
                        <div class="text-center">
                            <span class="text-3xl font-bold text-green-600">{{ \App\Models\Programa::count() }}</span>
                            <p class="text-sm text-gray-500 mt-1">Programas</p>
                        </div>
                        <div class="text-center">
                            <span class="text-3xl font-bold text-purple-600">{{ \App\Models\Acao::count() }}</span>
                            <p class="text-sm text-gray-500 mt-1">Ações</p>
                        </div>
                        <div class="text-center">
                            <span class="text-3xl font-bold text-orange-600">{{ \App\Models\User::count() }}</span>
                            <p class="text-sm text-gray-500 mt-1">Usuários</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <livewire:admin.lista-orcamentos-painel />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
