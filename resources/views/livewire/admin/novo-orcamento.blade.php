<div>
    {{-- Step Indicator --}}
    <div class="mb-8">
        <div class="flex items-center justify-center space-x-4">
            @foreach ([1 => 'Criar Orçamento', 2 => 'Importar Receita', 3 => 'Importar Despesa', 4 => 'Resumo'] as $num => $label)
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium
                        {{ $step >= $num ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                        {{ $num }}
                    </div>
                    <span class="ml-2 text-sm {{ $step >= $num ? 'text-blue-600 font-medium' : 'text-gray-400' }}">{{ $label }}</span>
                </div>
                @if ($num < 4)
                    <div class="w-12 h-0.5 {{ $step > $num ? 'bg-blue-600' : 'bg-gray-200' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Step 1: Criar Orçamento --}}
    @if ($step === 1)
        <div class="max-w-xl mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Novo Orçamento</h3>
            <form wire:submit="criarOrcamento" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Ano</label>
                        <input wire:model="ano" type="number" min="2024" max="2050"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @error('ano') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tipo</label>
                        <select wire:model.live="tipo"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="LOA">LOA</option>
                            <option value="PPA">PPA</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Prazo para Preenchimento</label>
                    <input wire:model="prazo_preenchimento" type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    @error('prazo_preenchimento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                @if ($tipo === 'PPA')
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Período PPA - Início</label>
                            <input wire:model="periodo_ppa_inicio" type="number"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @error('periodo_ppa_inicio') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Período PPA - Fim</label>
                            <input wire:model="periodo_ppa_fim" type="number"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @error('periodo_ppa_fim') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                @endif

                <div class="flex justify-end pt-4">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                        Criar e Continuar
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Step 2: Importar Receita --}}
    @if ($step === 2)
        <div class="max-w-xl mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Importar Receita</h3>
            <p class="text-sm text-gray-500 mb-6">Importe a planilha de receitas (colunas: Natureza da Receita, Descrição, Fonte de Recurso, Valor)</p>

            @if ($receitaResult)
                <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4">
                    <p class="text-green-700 font-medium">Receita importada com sucesso!</p>
                    <p class="text-green-600 text-sm">{{ $receitaResult['count'] }} receitas importadas. Total: R$ {{ number_format($receitaResult['total'] / 100, 2, ',', '.') }}</p>
                </div>
            @endif

            <form wire:submit="importarReceita" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Arquivo Excel (.xlsx)</label>
                    <input wire:model="receitaFile" type="file" accept=".xlsx,.xls,.csv"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('receitaFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    <div wire:loading wire:target="receitaFile" class="text-sm text-blue-600 mt-1">Carregando arquivo...</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Projeção da Receita (%)</label>
                    <p class="text-xs text-gray-400 mb-1">Deixe vazio ou 0 para não aplicar. Use valores positivos para aumento ou negativos para redução.</p>
                    <input wire:model="percentualProjecao" type="number" step="0.01" placeholder="Ex: 5 para +5%"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                </div>

                <div class="flex justify-between pt-4">
                    <div class="flex gap-2">
                        <button type="button" wire:click="voltarStep"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition">
                            &larr; Voltar
                        </button>
                        <button type="button" wire:click="pularReceita"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition">
                            Pular
                        </button>
                    </div>
                    <button type="submit"
                        class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="importarReceita">Importar Receita</span>
                        <span wire:loading wire:target="importarReceita">Importando...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Step 3: Importar Despesa --}}
    @if ($step === 3)
        <div class="max-w-xl mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Importar Despesa</h3>
            <p class="text-sm text-gray-500 mb-6">Importe a planilha de despesas. O sistema detecta automaticamente o formato e aplica as regras de substituição de fontes.</p>

            <form wire:submit="importarDespesa" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Arquivo Excel (.xlsx)</label>
                    <input wire:model="despesaFile" type="file" accept=".xlsx,.xls,.csv"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    @error('despesaFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    <div wire:loading wire:target="despesaFile" class="text-sm text-blue-600 mt-1">Carregando arquivo...</div>
                </div>

                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 text-sm">
                    <p class="text-yellow-700 font-medium">Regras que serão aplicadas automaticamente:</p>
                    <ul class="text-yellow-600 mt-1 list-disc list-inside">
                        <li>Fontes iniciando com "2" serão substituídas por "1" (ex: 2500 → 1500)</li>
                        <li>Naturezas duplicadas na mesma ação serão removidas</li>
                    </ul>
                </div>

                <div class="flex justify-between pt-4">
                    <button type="button" wire:click="voltarStep"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition">
                        &larr; Voltar
                    </button>
                    <button type="submit"
                        class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="importarDespesa">Importar Despesa</span>
                        <span wire:loading wire:target="importarDespesa">Importando...</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Step 4: Resumo --}}
    @if ($step === 4)
        <div class="max-w-2xl mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Resumo da Importação</h3>

            @if ($orcamento)
                <div class="bg-white border rounded-lg p-6 mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">Orçamento {{ $orcamento->tipo->value }} {{ $orcamento->ano }}</h4>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Prazo</dt>
                            <dd class="font-medium">{{ $orcamento->prazo_preenchimento?->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Status</dt>
                            <dd class="font-medium text-green-600">Aberto</dd>
                        </div>
                    </dl>
                </div>
            @endif

            @if ($receitaResult)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <h4 class="font-medium text-blue-800">Receitas</h4>
                    <p class="text-blue-600 text-sm">{{ $receitaResult['count'] }} receitas importadas</p>
                </div>
            @endif

            @if ($despesaResult)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <h4 class="font-medium text-green-800">Despesas</h4>
                    <p class="text-green-600 text-sm">{{ $despesaResult['created'] }} despesas importadas</p>
                    @if ($despesaResult['duplicates'] > 0)
                        <p class="text-yellow-600 text-sm">{{ $despesaResult['duplicates'] }} duplicatas removidas</p>
                    @endif
                    @if (count($despesaResult['errors'] ?? []) > 0)
                        <details class="mt-2">
                            <summary class="text-red-600 text-sm cursor-pointer">{{ count($despesaResult['errors']) }} erros</summary>
                            <ul class="mt-1 text-red-500 text-xs list-disc list-inside">
                                @foreach (array_slice($despesaResult['errors'], 0, 10) as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </details>
                    @endif
                </div>
            @endif

            <div class="flex justify-between pt-4">
                <button type="button" wire:click="voltarStep"
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition">
                    &larr; Voltar
                </button>
                <button wire:click="finalizar"
                    class="inline-flex items-center px-6 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                    Concluir e Ir à Parametrização
                </button>
            </div>
        </div>
    @endif
</div>
