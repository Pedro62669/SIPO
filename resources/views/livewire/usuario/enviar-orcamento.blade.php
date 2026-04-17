@php
    use App\Enums\EnvioOrcamentoStatus;
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
    @elseif (!$unidade)
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
            <p class="text-yellow-800">Seu usuário não está vinculado a uma unidade.</p>
        </div>
    @else
        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800 border border-green-200">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Resumo para envio</h3>
            <p class="text-sm text-gray-600 mt-1">
                {{ $unidade->descricao }} · LOA {{ $orcamento->ano }}
            </p>
            @if ($envio?->status === EnvioOrcamentoStatus::Enviado)
                <p class="mt-3 text-sm text-emerald-700 font-medium">
                    Orçamento já enviado em {{ $envio->enviado_em?->format('d/m/Y H:i') ?? '—' }}.
                </p>
            @endif
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h4 class="font-medium text-gray-900">Valores por classificação</h4>
                <p class="text-xs text-gray-500 mt-1">Valores em reais (R$). Liberado e preenchido são comparados na mesma base (parametrização em centavos e LOA em reais inteiros).</p>
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
                        @forelse ($rows as $row)
                            <tr wire:key="row-{{ $row['classificacao'] }}">
                                <td class="px-4 py-3 text-gray-900">{{ $classificacaoLabel($row['classificacao']) }}</td>
                                <td class="px-4 py-3 text-right text-gray-800">
                                    R$ {{ number_format($row['liberado_centavos'] / 100, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right text-gray-800">
                                    R$ {{ number_format($row['preenchido_centavos'] / 100, 2, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-medium @if ($row['diferenca_centavos'] < 0) text-red-600 @elseif ($row['diferenca_centavos'] > 0) text-amber-700 @else text-gray-600 @endif">
                                    R$ {{ number_format($row['diferenca_centavos'] / 100, 2, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    Não há dados de parametrização ou preenchimento para exibir.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($rows->isNotEmpty())
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr>
                                <td class="px-4 py-3 text-gray-900">Total</td>
                                <td class="px-4 py-3 text-right">R$ {{ number_format($totalLiberadoCentavos / 100, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">R$ {{ number_format($totalPreenchidoCentavos / 100, 2, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right @if ($totalLiberadoCentavos - $totalPreenchidoCentavos < 0) text-red-600 @else text-gray-800 @endif">
                                    R$ {{ number_format(($totalLiberadoCentavos - $totalPreenchidoCentavos) / 100, 2, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="button" wire:click="enviar" wire:loading.attr="disabled"
                @disabled($envio?->status === EnvioOrcamentoStatus::Enviado)
                class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50">
                <span wire:loading.remove wire:target="enviar">Enviar orçamento</span>
                <span wire:loading wire:target="enviar">Enviando…</span>
            </button>
        </div>
    @endif
</div>
