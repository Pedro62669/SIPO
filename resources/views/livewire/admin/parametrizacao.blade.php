<div class="space-y-6" x-data="{ tab: @entangle('activeTab') }" x-cloak>
    @if (session()->has('message'))
        <div class="rounded-md bg-green-50 p-4 border border-green-200 text-green-800 text-sm" role="alert">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">
                Orçamento {{ $orcamento->tipo->value }} {{ $orcamento->ano }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Status: <span class="font-medium text-gray-700">{{ $orcamento->status->value }}</span>
                @if ($orcamento->prazo_preenchimento)
                    · Prazo geral: {{ $orcamento->prazo_preenchimento->format('d/m/Y') }}
                @endif
            </p>
        </div>

        {{-- Abas --}}
        <div class="border-b border-gray-200 px-6">
            <nav class="-mb-px flex flex-wrap gap-2 sm:gap-6" aria-label="Seções">
                <button type="button" @click="tab = 'fontes'"
                    :class="tab === 'fontes'
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    Fontes
                </button>
                <button type="button" @click="tab = 'valores'"
                    :class="tab === 'valores'
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    Valores por Secretaria
                </button>
                <button type="button" @click="tab = 'prazos'"
                    :class="tab === 'prazos'
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    Prazos
                </button>
                <button type="button" @click="tab = 'liberar'"
                    :class="tab === 'liberar'
                        ? 'border-blue-500 text-blue-600'
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                    Liberar
                </button>
            </nav>
        </div>

        <div class="p-6 space-y-8">
            {{-- Tab Fontes --}}
            <div x-show="tab === 'fontes'" x-transition.opacity class="space-y-8">
                <section>
                    <h4 class="text-base font-semibold text-gray-900 mb-4">Regras de substituição de fonte</h4>
                    <p class="text-sm text-gray-500 mb-4">Define para qual código de fonte cada fonte de origem deve ser mapeada neste orçamento.</p>

                    <form wire:submit="addRegraFonte" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end mb-6">
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Fonte origem</label>
                            <input type="text" wire:model="fonteOrigem" maxlength="10"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                placeholder="Ex.: 1500">
                            @error('fonteOrigem') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Fonte destino</label>
                            <input type="text" wire:model="fonteDestino" maxlength="10"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                placeholder="Ex.: 1600">
                            @error('fonteDestino') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit"
                                class="w-full sm:w-auto inline-flex justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                Adicionar
                            </button>
                        </div>
                    </form>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-600">Origem</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-600">Destino</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-600 w-24">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($regrasFonte as $regra)
                                    <tr wire:key="regra-{{ $regra->id }}">
                                        <td class="px-4 py-2 font-mono">{{ $regra->fonte_origem }}</td>
                                        <td class="px-4 py-2 font-mono">{{ $regra->fonte_destino }}</td>
                                        <td class="px-4 py-2 text-right">
                                            <button type="button" wire:click="deleteRegraFonte({{ $regra->id }})"
                                                wire:confirm="Remover esta regra?"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                Excluir
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">Nenhuma regra cadastrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section>
                    <h4 class="text-base font-semibold text-gray-900 mb-4">Restrições de faixa de fonte por secretaria</h4>
                    <p class="text-sm text-gray-500 mb-4">Limita o intervalo de códigos de fonte utilizável por unidade (e opcionalmente subunidade).</p>

                    <form wire:submit="addRestricaoFonte" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fonte (início)</label>
                            <input type="text" wire:model="restricaoFonteInicio" maxlength="10"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @error('restricaoFonteInicio') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fonte (fim)</label>
                            <input type="text" wire:model="restricaoFonteFim" maxlength="10"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @error('restricaoFonteFim') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Secretaria (unidade)</label>
                            <select wire:model.live="restricaoUnidadeId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Selecione…</option>
                                @foreach ($unidades as $u)
                                    <option value="{{ $u->id }}">{{ $u->codigo }} — {{ $u->descricao }}</option>
                                @endforeach
                            </select>
                            @error('restricaoUnidadeId') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subunidade (opcional)</label>
                            <select wire:model="restricaoSubunidadeId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                @disabled(! $restricaoUnidadeId)>
                                <option value="">Todas / nenhuma</option>
                                @foreach ($subunidadesRestricao as $s)
                                    <option value="{{ $s->id }}">{{ $s->codigo }} — {{ $s->descricao }}</option>
                                @endforeach
                            </select>
                            @error('restricaoSubunidadeId') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2 lg:col-span-3 flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                Adicionar restrição
                            </button>
                        </div>
                    </form>

                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-600">Intervalo</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-600">Unidade</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-600">Subunidade</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-600 w-24">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($restricoesFonte as $r)
                                    <tr wire:key="rest-{{ $r->id }}">
                                        <td class="px-4 py-2 font-mono">{{ $r->fonte_recurso_inicio }} → {{ $r->fonte_recurso_fim }}</td>
                                        <td class="px-4 py-2">{{ $r->unidade?->codigo }} — {{ $r->unidade?->descricao }}</td>
                                        <td class="px-4 py-2">
                                            @if ($r->subunidade)
                                                {{ $r->subunidade->codigo }} — {{ $r->subunidade->descricao }}
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <button type="button" wire:click="deleteRestricaoFonte({{ $r->id }})"
                                                wire:confirm="Remover esta restrição?"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                Excluir
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">Nenhuma restrição cadastrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            {{-- Tab Valores --}}
            <div x-show="tab === 'valores'" x-transition.opacity class="space-y-8">
                <section>
                    <h4 class="text-base font-semibold text-gray-900 mb-2">Valores liberados por secretaria</h4>
                    <p class="text-sm text-gray-500 mb-4">
                        Os valores liberados são limitados pela <strong>receita importada na etapa do orçamento</strong>, por <strong>fonte de recurso</strong> (após as regras de substituição na aba Fontes).
                        Informe o valor em <strong>centavos</strong> (mesma base da receita no banco). Ex.: R$ 1.234,56 → <span class="font-mono">123456</span>.
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                            <p class="text-xs text-gray-500 uppercase">Receita total importada</p>
                            <p class="text-lg font-semibold text-gray-900">R$ {{ number_format($totReceitaCentavos / 100, 2, ',', '.') }}</p>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-blue-50/50 p-4">
                            <p class="text-xs text-blue-800 uppercase">Total já liberado</p>
                            <p class="text-lg font-semibold text-blue-900">R$ {{ number_format($totLiberadoCentavos / 100, 2, ',', '.') }}</p>
                        </div>
                        <div class="rounded-lg border p-4 @if ($totLiberadoCentavos > $totReceitaCentavos) border-red-200 bg-red-50 @else border-emerald-200 bg-emerald-50/50 @endif">
                            <p class="text-xs uppercase @if ($totLiberadoCentavos > $totReceitaCentavos) text-red-800 @else text-emerald-800 @endif">Saldo global</p>
                            <p class="text-lg font-semibold @if ($totLiberadoCentavos > $totReceitaCentavos) text-red-800 @else text-emerald-900 @endif">
                                R$ {{ number_format(($totReceitaCentavos - $totLiberadoCentavos) / 100, 2, ',', '.') }}
                            </p>
                        </div>
                    </div>

                    @if ($totReceitaCentavos === 0)
                        <div class="rounded-md bg-amber-50 border border-amber-200 p-4 text-amber-900 text-sm mb-6">
                            Nenhuma receita importada para este orçamento. Importe a planilha de receitas em <strong>Novo orçamento</strong> antes de liberar valores; caso contrário o teto por fonte será R$ 0,00.
                        </div>
                    @endif

                    @php
                        $fontesVisiveis = $fontes->filter(function ($f) use ($receitaPorFonte) {
                            $pf = $receitaPorFonte[$f->id] ?? ['receita_centavos' => 0, 'liberado_centavos' => 0, 'saldo_centavos' => 0];

                            return $pf['receita_centavos'] > 0 || $pf['liberado_centavos'] > 0 || $pf['saldo_centavos'] > 0;
                        })->values();
                    @endphp

                    <div class="overflow-x-auto rounded-lg border border-gray-200 mb-6">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-600">Fonte</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-600">Receita importada</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-600">Já liberado</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-600">Saldo</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($fontesVisiveis as $f)
                                    @php
                                        $pf = $receitaPorFonte[$f->id] ?? ['receita_centavos' => 0, 'liberado_centavos' => 0, 'saldo_centavos' => 0];
                                    @endphp
                                    <tr wire:key="ref-fonte-{{ $f->id }}">
                                        <td class="px-4 py-2 font-mono text-xs">{{ $f->codigo }} — {{ \Illuminate\Support\Str::limit($f->descricao, 36) }}</td>
                                        <td class="px-4 py-2 text-right">R$ {{ number_format($pf['receita_centavos'] / 100, 2, ',', '.') }}</td>
                                        <td class="px-4 py-2 text-right">R$ {{ number_format($pf['liberado_centavos'] / 100, 2, ',', '.') }}</td>
                                        <td class="px-4 py-2 text-right font-medium @if ($pf['saldo_centavos'] <= 0 && $pf['receita_centavos'] > 0) text-amber-700 @elseif ($pf['saldo_centavos'] < 0) text-red-700 @else text-emerald-800 @endif">
                                            R$ {{ number_format($pf['saldo_centavos'] / 100, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                                            Nenhuma fonte com receita importada ou saldo disponível.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <form wire:submit="addParametrizacao" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Secretaria (unidade)</label>
                            <select wire:model.live="paramUnidadeId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Selecione…</option>
                                @foreach ($unidades as $u)
                                    <option value="{{ $u->id }}">{{ $u->codigo }} — {{ $u->descricao }}</option>
                                @endforeach
                            </select>
                            @error('paramUnidadeId') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subunidade (opcional)</label>
                            <select wire:model="paramSubunidadeId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                @disabled(! $paramUnidadeId)>
                                <option value="">Nenhuma</option>
                                @foreach ($subunidadesParam as $s)
                                    <option value="{{ $s->id }}">{{ $s->codigo }} — {{ $s->descricao }}</option>
                                @endforeach
                            </select>
                            @error('paramSubunidadeId') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <label class="block text-sm font-medium text-gray-700">Fonte de recurso</label>
                            <select wire:model.live="paramFonteId"
                                class="mt-1 block w-full max-w-xl rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Selecione…</option>
                                @foreach ($fontesParametrizacao as $f)
                                    <option value="{{ $f->id }}">{{ $f->codigo }} — {{ $f->descricao }}</option>
                                @endforeach
                            </select>
                            @error('paramFonteId') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                            @if ($paramUnidadeId && $fontesParametrizacao->isEmpty())
                                <p class="mt-2 text-xs text-amber-700">
                                    Nenhuma fonte permitida para a secretaria selecionada.
                                </p>
                            @endif
                            @if ($paramFonteId && isset($receitaPorFonte[(int) $paramFonteId]))
                                @php $pf = $receitaPorFonte[(int) $paramFonteId]; @endphp
                                <p class="mt-2 text-xs text-gray-600 space-y-0.5">
                                    <span class="block">Receita importada (após regras): <strong>R$ {{ number_format($pf['receita_centavos'] / 100, 2, ',', '.') }}</strong></span>
                                    <span class="block">Já liberado nesta fonte: <strong>R$ {{ number_format($pf['liberado_centavos'] / 100, 2, ',', '.') }}</strong></span>
                                    <span class="block text-emerald-800">Disponível para novo lançamento: <strong>R$ {{ number_format($pf['saldo_centavos'] / 100, 2, ',', '.') }}</strong></span>
                                </p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Classificação</label>
                            <select wire:model="paramClassificacao"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="geral">Geral</option>
                                <option value="custeio">Custeio</option>
                                <option value="pessoal">Pessoal</option>
                                <option value="investimento">Investimento</option>
                                <option value="terceirizacao">Terceirização</option>
                            </select>
                            @error('paramClassificacao') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">% exercício anterior (opcional)</label>
                            <input type="number" step="0.01" min="0" wire:model="paramPercentualAnterior"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @error('paramPercentualAnterior') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Valor liberado (centavos)</label>
                            <input type="number" min="0" step="1" wire:model.live="paramValorLiberado"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @if ($paramValorLiberado > 0)
                                <p class="mt-1 text-xs text-gray-500">
                                    Equivalente: R$ {{ number_format($paramValorLiberado / 100, 2, ',', '.') }}
                                </p>
                            @endif
                            @error('paramValorLiberado') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2 lg:col-span-3 flex justify-end">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                Adicionar parametrização
                            </button>
                        </div>
                    </form>

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
                                    <th class="px-4 py-2 text-right font-medium text-gray-600 w-24">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($parametrizacoes as $p)
                                    <tr wire:key="param-{{ $p->id }}">
                                        <td class="px-4 py-2">{{ $p->unidade?->codigo }} — {{ $p->unidade?->descricao }}</td>
                                        <td class="px-4 py-2">
                                            @if ($p->subunidade)
                                                {{ $p->subunidade->codigo }} — {{ $p->subunidade->descricao }}
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
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
                                        <td class="px-4 py-2 text-right">
                                            {{ $p->percentual_anterior !== null ? number_format((float) $p->percentual_anterior, 2, ',', '.') : '—' }}
                                        </td>
                                        <td class="px-4 py-2 text-right whitespace-nowrap font-medium">
                                            R$ {{ number_format($p->valor_liberado / 100, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2 text-right">
                                            <button type="button" wire:click="deleteParametrizacao({{ $p->id }})"
                                                wire:confirm="Remover esta parametrização?"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                Excluir
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">Nenhum registro.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            {{-- Tab Prazos --}}
            <div x-show="tab === 'prazos'" x-transition.opacity class="space-y-6">
                <section>
                    <h4 class="text-base font-semibold text-gray-900 mb-2">Prazo estendido por secretaria</h4>
                    <p class="text-sm text-gray-500 mb-4">Define uma data limite adicional para uma unidade específica (deve ser posterior a hoje).</p>

                    <form wire:submit="addPrazo" class="grid grid-cols-1 md:grid-cols-2 gap-4 items-end mb-6 max-w-3xl">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Secretaria</label>
                            <select wire:model="prazoUnidadeId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Selecione…</option>
                                @foreach ($unidades as $u)
                                    <option value="{{ $u->id }}">{{ $u->codigo }} — {{ $u->descricao }}</option>
                                @endforeach
                            </select>
                            @error('prazoUnidadeId') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prazo estendido</label>
                            <input type="date" wire:model="prazoEstendido"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @error('prazoEstendido') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition">
                                Salvar prazo
                            </button>
                        </div>
                    </form>

                    <div class="overflow-x-auto rounded-lg border border-gray-200 max-w-3xl">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-600">Unidade</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-600">Prazo estendido</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-600 w-24">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse ($prazos as $pz)
                                    <tr wire:key="prazo-{{ $pz->id }}">
                                        <td class="px-4 py-2">{{ $pz->unidade?->codigo }} — {{ $pz->unidade?->descricao }}</td>
                                        <td class="px-4 py-2">{{ $pz->prazo_estendido->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2 text-right">
                                            <button type="button" wire:click="deletePrazo({{ $pz->id }})"
                                                wire:confirm="Remover este prazo?"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                Excluir
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-500">Nenhum prazo estendido.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            {{-- Tab Liberar --}}
            <div x-show="tab === 'liberar'" x-transition.opacity class="space-y-4">
                <h4 class="text-base font-semibold text-gray-900">Liberar para preenchimento</h4>
                <p class="text-sm text-gray-600 max-w-2xl">
                    Ao confirmar, o orçamento fica com status <strong>aberto</strong> para que as secretarias possam preencher conforme os parâmetros definidos.
                    Revise regras de fonte, restrições, valores e prazos antes de liberar.
                </p>
                <div class="pt-2">
                    <button type="button" wire:click="liberarPreenchimento"
                        wire:confirm="Confirma a liberação do orçamento para preenchimento pelas secretarias?"
                        class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-wide hover:bg-green-700 transition shadow-sm">
                        Liberar orçamento para preenchimento
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
