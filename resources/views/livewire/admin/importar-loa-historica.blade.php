<div>
    {{-- Step Indicator --}}
    <div class="mb-8">
        <div class="flex items-center justify-center space-x-4">
            @foreach ([1 => 'Ano / LOA', 2 => 'Importar Despesas', 3 => 'Importar Saldos', 4 => 'Importar Parametrização', 5 => 'Concluído'] as $num => $label)
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium
                        {{ $step >= $num ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600' }}">
                        {{ $num }}
                    </div>
                    <span class="ml-2 text-sm {{ $step >= $num ? 'text-indigo-600 font-medium' : 'text-gray-400' }}">{{ $label }}</span>
                </div>
                @if ($num < 5)
                    <div class="w-12 h-0.5 {{ $step > $num ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Step 1: Ano --}}
    @if ($step === 1)
        <div class="max-w-md mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Identificar a LOA Histórica</h3>
            <p class="text-sm text-gray-500 mb-6">Informe o ano da LOA que deseja importar como referência histórica. Os dados ficarão disponíveis para consulta posterior.</p>

            <form wire:submit="criarOrcamentoHistorico" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ano da LOA</label>
                    <input wire:model="ano" type="number" min="2000" max="2100"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('ano') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 text-sm text-blue-700">
                    Será criada uma LOA histórica (somente leitura). As regras de substituição de fonte <strong>não serão aplicadas</strong>, preservando os dados originais.
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                        Continuar
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Step 2: Importar Despesa --}}
    @if ($step === 2)
        <div class="max-w-xl mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Importar Despesas — LOA {{ $orcamento?->ano }}</h3>
            <p class="text-sm text-gray-500 mb-6">
                Importe a planilha de descrição das despesas (mesmo formato do arquivo de despesas — colunas: Ano, Despesa, Natureza, Fonte, Unidade, Subunidade, Programa, Ação).
            </p>

            @if ($despesaResult)
                <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
                    <p class="text-green-700 font-medium">{{ $despesaResult['created'] }} despesas importadas.</p>
                    @if ($despesaResult['duplicates'] > 0)
                        <p class="text-yellow-600 text-sm">{{ $despesaResult['duplicates'] }} duplicatas ignoradas.</p>
                    @endif
                </div>
            @endif

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Arquivo Excel (.xlsx)</label>
                    <input wire:model="despesaFile" type="file" accept=".xlsx,.xls,.csv"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    @error('despesaFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div wire:loading wire:target="despesaFile,importarDespesa"
                    class="flex items-center gap-3 bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <div>
                        <span wire:loading wire:target="despesaFile" class="text-indigo-700 text-sm font-medium">Enviando arquivo ao servidor...</span>
                        <span wire:loading wire:target="importarDespesa" class="text-indigo-700 text-sm font-medium">Processando despesas, aguarde...</span>
                    </div>
                </div>

                <div wire:loading.remove wire:target="despesaFile,importarDespesa" class="pt-1">
                    <button type="button" wire:click="voltarStep"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition">
                        &larr; Voltar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Step 3: Importar Saldo --}}
    @if ($step === 3)
        <div class="max-w-xl mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Importar Saldos Financeiros — LOA {{ $orcamento?->ano }}</h3>
            <p class="text-sm text-gray-500 mb-6">
                Importe a planilha de saldo das despesas para registrar os valores de dotação inicial, empenhado e liquidado.
                O sistema vincula cada linha pelo número da despesa.
            </p>

            @if ($saldoResult)
                <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
                    <p class="text-green-700 font-medium">{{ $saldoResult['updated'] }} saldos atualizados.</p>
                    @if ($saldoResult['not_found'] > 0)
                        <p class="text-yellow-600 text-sm">{{ $saldoResult['not_found'] }} linhas sem despesa correspondente.</p>
                    @endif
                </div>
            @endif

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 text-sm text-yellow-700 mb-4">
                <p class="font-medium">Formato esperado da planilha de saldo:</p>
                <p class="mt-1">Coluna obrigatória: <strong>Despesa</strong> (número que vincula à planilha de estrutura).</p>
                <p class="mt-1">Colunas financeiras reconhecidas: Dotação Inicial, Crédito Suplementar, Crédito Especial, Redução de Créditos, Dotação Atualizada, Empenhado, Liquidado, Pago, Saldo da Dotação, Saldo Disponível, entre outras.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Arquivo Excel (.xlsx)</label>
                    <input wire:model="saldoFile" type="file" accept=".xlsx,.xls,.csv"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    @error('saldoFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div wire:loading wire:target="saldoFile,importarSaldo"
                    class="flex items-center gap-3 bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <div>
                        <span wire:loading wire:target="saldoFile" class="text-indigo-700 text-sm font-medium">Enviando arquivo ao servidor...</span>
                        <span wire:loading wire:target="importarSaldo" class="text-indigo-700 text-sm font-medium">Processando saldos, aguarde...</span>
                    </div>
                </div>

                <div wire:loading.remove wire:target="saldoFile,importarSaldo" class="flex justify-start gap-2 pt-1">
                    <button type="button" wire:click="voltarStep"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition">
                        &larr; Voltar
                    </button>
                    <button type="button" wire:click="pularSaldo"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition">
                        Pular (sem saldos)
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Step 4: Importar Parametrização --}}
    @if ($step === 4)
        <div class="max-w-xl mx-auto">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Importar Parametrização — LOA {{ $orcamento?->ano }}</h3>
            <p class="text-sm text-gray-500 mb-6">
                Importe a planilha de valores liberados por secretaria para enriquecer a consulta histórica.
                Colunas aceitas: unidade/secretaria, subunidade, fonte, classificação, percentual anterior e valor liberado.
            </p>

            @if ($parametrizacaoResult)
                <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
                    <p class="text-green-700 font-medium">{{ $parametrizacaoResult['created'] }} parametrizações importadas.</p>
                    @if (count($parametrizacaoResult['errors'] ?? []) > 0)
                        <p class="text-yellow-700 text-sm mt-1">{{ count($parametrizacaoResult['errors']) }} linhas não puderam ser importadas.</p>
                    @endif
                </div>
            @endif

            <div class="bg-indigo-50 border-l-4 border-indigo-400 p-4 text-sm text-indigo-800 mb-4">
                <p class="font-medium">Formato recomendado:</p>
                <p class="mt-1">Informe pelo menos <strong>Unidade/Secretaria</strong>, <strong>Fonte</strong> e <strong>Valor liberado</strong>.</p>
                <p class="mt-1">Se a coluna de classificação não existir, o sistema importará como <strong>Geral</strong>.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Arquivo Excel (.xlsx)</label>
                    <input wire:model="parametrizacaoFile" type="file" accept=".xlsx,.xls,.csv"
                        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    @error('parametrizacaoFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div wire:loading wire:target="parametrizacaoFile,importarParametrizacao"
                    class="flex items-center gap-3 bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                    <svg class="animate-spin h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    <div>
                        <span wire:loading wire:target="parametrizacaoFile" class="text-indigo-700 text-sm font-medium">Enviando arquivo ao servidor...</span>
                        <span wire:loading wire:target="importarParametrizacao" class="text-indigo-700 text-sm font-medium">Processando parametrizações, aguarde...</span>
                    </div>
                </div>

                <div wire:loading.remove wire:target="parametrizacaoFile,importarParametrizacao" class="flex justify-start gap-2 pt-1">
                    <button type="button" wire:click="voltarStep"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition">
                        &larr; Voltar
                    </button>
                    <button type="button" wire:click="pularParametrizacao"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition">
                        Pular (sem parametrização)
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Step 5: Concluído --}}
    @if ($step === 5)
        <div class="max-w-lg mx-auto text-center">
            <div class="flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">LOA {{ $orcamento?->ano }} importada com sucesso!</h3>
            <p class="text-sm text-gray-500 mb-6">Os dados estão disponíveis para consulta na seção "Consultar LOAs Anteriores".</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8 text-left">
                @if ($despesaResult)
                    <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
                        <p class="text-xs text-indigo-500 uppercase font-medium">Despesas</p>
                        <p class="text-2xl font-bold text-indigo-700">{{ $despesaResult['created'] }}</p>
                        <p class="text-xs text-indigo-500">registros importados</p>
                    </div>
                @endif
                @if ($saldoResult)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <p class="text-xs text-green-500 uppercase font-medium">Saldos</p>
                        <p class="text-2xl font-bold text-green-700">{{ $saldoResult['updated'] }}</p>
                        <p class="text-xs text-green-500">linhas vinculadas</p>
                    </div>
                    @if ($saldoResult['not_found'] > 0)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-xs text-yellow-500 uppercase font-medium">Sem vínculo</p>
                            <p class="text-2xl font-bold text-yellow-700">{{ $saldoResult['not_found'] }}</p>
                            <p class="text-xs text-yellow-500">linhas do saldo não encontradas</p>
                        </div>
                    @endif
                @endif
                @if ($parametrizacaoResult)
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-xs text-blue-500 uppercase font-medium">Parametrização</p>
                        <p class="text-2xl font-bold text-blue-700">{{ $parametrizacaoResult['created'] }}</p>
                        <p class="text-xs text-blue-500">linhas importadas</p>
                    </div>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button type="button" wire:click="voltarStep"
                    class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition">
                    &larr; Voltar
                </button>
                <a href="{{ route('admin.loa-historica.consultar') }}"
                    class="inline-flex items-center justify-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                    Consultar LOAs Anteriores
                </a>
                <a href="{{ route('admin.loa-historica.importar') }}"
                    class="inline-flex items-center justify-center px-6 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition">
                    Importar Outro Ano
                </a>
            </div>
        </div>
    @endif
</div>
