<div>
    @if (session('status'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800 border border-green-200">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Orçamentos Criados</h3>
        <a href="{{ route('admin.orcamento.novo') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
            Criar novo
        </a>
    </div>

    @if ($orcamentos->isEmpty())
        <p class="text-sm text-gray-500">Nenhum orçamento criado até o momento.</p>
    @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Ano</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Tipo</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Prazo</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Criado em</th>
                        <th class="px-4 py-3 text-right font-semibold text-gray-600">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach ($orcamentos as $orcamento)
                        <tr wire:key="orcamento-{{ $orcamento->id }}">
                            <td class="px-4 py-3 text-gray-900 font-medium">{{ $orcamento->ano }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $orcamento->tipo->value }}</td>
                            <td class="px-4 py-3">
                                @if ($orcamento->status === \App\Enums\OrcamentoStatus::Aberto)
                                    <span class="inline-flex rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-700">Aberto</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-semibold text-gray-700">Finalizado</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-700">{{ $orcamento->prazo_preenchimento?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ $orcamento->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.orcamento.parametrizacao', ['orcamento' => $orcamento->id]) }}"
                                    class="inline-block align-baseline mr-5 text-blue-600 hover:text-blue-800 font-medium"
                                    wire:navigate>
                                    Parametrização
                                </a>
                                <a href="{{ route('admin.orcamento.cortes', ['orcamento' => $orcamento->id]) }}"
                                    class="inline-block align-baseline mr-5 text-indigo-600 hover:text-indigo-800 font-medium"
                                    wire:navigate>
                                    Cortes
                                </a>
                                <button
                                    type="button"
                                    wire:click="excluir({{ $orcamento->id }})"
                                    wire:confirm="Excluir definitivamente o orçamento {{ $orcamento->tipo->value }} {{ $orcamento->ano }}? Receitas, despesas importadas, LOA, parametrizações e demais dados vinculados serão removidos."
                                    class="inline-block align-baseline text-red-600 hover:text-red-800 font-medium border-0 bg-transparent p-0 cursor-pointer"
                                >
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $orcamentos->links() }}
        </div>
    @endif
</div>
