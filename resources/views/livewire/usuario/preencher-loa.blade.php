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
        {{-- Cabeçalho --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6 border-b border-gray-100">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">
                            LOA {{ $orcamento->ano }}
                            <span class="text-sm font-normal text-gray-500">· {{ $orcamento->tipo->value }}</span>
                        </h3>
                        <p class="text-sm text-gray-600 mt-1">
                            Status do orçamento:
                            <span class="font-medium capitalize">{{ str_replace('_', ' ', $orcamento->status->value) }}</span>
                        </p>
                        <p class="text-sm text-gray-600">
                            Prazo:
                            {{ $orcamento->prazo_preenchimento?->format('d/m/Y') ?? 'Não definido' }}
                        </p>
                        <p class="text-sm text-gray-700 mt-2">
                            <span class="font-medium">Unidade:</span> {{ $unidade->descricao }}
                        </p>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:text-right">
                        <div class="rounded-lg bg-gray-50 p-3">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Total liberado</p>
                            <p class="text-lg font-bold text-gray-900">R$ {{ number_format($totalLiberadoCentavos / 100, 2, ',', '.') }}</p>
                        </div>
                        <div class="rounded-lg bg-blue-50 p-3">
                            <p class="text-xs text-blue-700 uppercase tracking-wide">Total preenchido</p>
                            <p class="text-lg font-bold text-blue-900">R$ {{ number_format($totalPreenchidoCentavos / 100, 2, ',', '.') }}</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 p-3">
                            <p class="text-xs text-emerald-700 uppercase tracking-wide">Execução</p>
                            <p class="text-lg font-bold text-emerald-900">{{ $percentualExecucao }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Subunidade --}}
        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
            <label for="subunidade" class="block text-sm font-medium text-gray-700 mb-2">Subunidade</label>
            <select id="subunidade" wire:model.live="subunidadeId"
                class="block w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                <option value="">Selecione...</option>
                @foreach ($subunidades as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->codigo }} — {{ $sub->descricao }}</option>
                @endforeach
            </select>
            @if ($subunidades->isEmpty())
                <p class="mt-2 text-sm text-amber-600">Nenhuma subunidade cadastrada para esta unidade.</p>
            @endif
        </div>

        @if ($subunidadeId)
            @forelse ($loaAcoes as $loaAcao)
                <div class="bg-white shadow-sm sm:rounded-lg mb-4 overflow-hidden" wire:key="loa-acao-{{ $loaAcao->id }}"
                    x-data="{ open: true }">
                    <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between px-6 py-4 text-left bg-gray-50 hover:bg-gray-100 border-b border-gray-200 transition">
                        <div>
                            <span class="font-semibold text-gray-900">{{ $loaAcao->nome }}</span>
                            @if ($loaAcao->acaoOriginal?->programa)
                                <span class="block text-sm text-gray-500 mt-0.5">
                                    Programa: {{ $loaAcao->acaoOriginal->programa->codigo }}
                                    — {{ $loaAcao->acaoOriginal->programa->descricao }}
                                </span>
                            @endif
                        </div>
                        <svg class="w-5 h-5 text-gray-500 transition-transform shrink-0" :class="{ 'rotate-180': open }"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open" x-cloak class="border-t border-gray-100">
                        <div class="p-4 sm:p-6 overflow-x-auto">
                            <div class="flex justify-end mb-3">
                                <button type="button" wire:click="openAddLine({{ $loaAcao->id }})"
                                    class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                                    Adicionar linha
                                </button>
                            </div>

                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">Natureza</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">Fonte</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">Detalhamento</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-600">Valor</th>
                                        <th class="px-3 py-2 text-left font-medium text-gray-600">Observação</th>
                                        <th class="px-3 py-2 text-right font-medium text-gray-600">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @php
                                        $lines = $preenchimentos->get($loaAcao->id, collect());
                                    @endphp
                                    @forelse ($lines as $line)
                                        <tr wire:key="line-{{ $line->id }}" @class(['bg-red-50/40' => $editingLineId === $line->id])>
                                            <td class="px-3 py-2 text-gray-800">
                                                {{ $line->natureza?->codigo }} — {{ Str::limit($line->natureza?->descricao ?? '—', 40) }}
                                            </td>
                                            <td class="px-3 py-2 text-gray-800">
                                                {{ $line->fonte?->codigo }} — {{ Str::limit($line->fonte?->descricao ?? '—', 35) }}
                                            </td>
                                            <td class="px-3 py-2 text-gray-600">{{ $line->detalhamento ?? '—' }}</td>
                                            <td class="px-3 py-2 text-right font-medium text-gray-900">
                                                @if ($editingLineId === $line->id)
                                                    <input type="text" inputmode="numeric"
                                                        x-data="{
                                                            display: '',
                                                            format(value) {
                                                                const amount = Number(value || 0);
                                                                return amount > 0 ? amount.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '';
                                                            },
                                                            update(value) {
                                                                const digits = String(value).replace(/\D/g, '').slice(0, 12);
                                                                const amount = digits ? parseInt(digits, 10) : 0;
                                                                this.display = this.format(amount);
                                                                $wire.set('editingValor', amount, false);
                                                            },
                                                        }"
                                                        x-init="display = format($wire.editingValor)"
                                                        x-model="display"
                                                        x-on:input="update($event.target.value)"
                                                        placeholder="R$ 0,00"
                                                        class="w-36 rounded-md border-gray-300 text-sm text-right">
                                                @else
                                                    R$ {{ number_format($line->valor, 0, ',', '.') }}
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-gray-600 max-w-xs">
                                                @if ($editingLineId === $line->id)
                                                    <textarea wire:model="editingObservacao" rows="2"
                                                        class="w-full rounded-md border-gray-300 text-sm"></textarea>
                                                @else
                                                    {{ $line->observacao ?? '—' }}
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                                @if ($editingLineId === $line->id)
                                                    <button type="button" wire:click="saveEdit"
                                                        class="text-green-600 hover:text-green-800 text-xs font-medium mr-2">Salvar</button>
                                                    <button type="button" wire:click="cancelEdit"
                                                        class="text-gray-500 hover:text-gray-700 text-xs">Cancelar</button>
                                                @else
                                                    <button type="button" wire:click="startEdit({{ $line->id }})"
                                                        class="text-blue-600 hover:text-blue-800 text-xs font-medium mr-2">Editar</button>
                                                    <button type="button" wire:click="deleteLine({{ $line->id }})"
                                                        wire:confirm="Excluir esta linha?"
                                                        class="text-red-600 hover:text-red-800 text-xs font-medium">Excluir</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-6 text-center text-gray-500">
                                                Nenhuma linha lançada. Use &quot;Adicionar linha&quot;.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-8 text-center text-gray-500">
                    Nenhuma ação da LOA para esta subunidade. Importe despesas ou cadastre ações em Metas e Ações.
                </div>
            @endforelse
        @endif
    @endif

    {{-- Modal nova linha --}}
    @if ($showAddLine)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity" wire:click="closeAddLine"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-lg w-full p-6" @click.stop>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Nova linha de despesa</h4>
                    <form wire:submit="saveLine" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Natureza</label>
                            <select wire:model="naturezaId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Selecione...</option>
                                @foreach ($naturezas as $n)
                                    <option value="{{ $n->id }}">{{ $n->codigo }} — {{ $n->descricao }}</option>
                                @endforeach
                            </select>
                            @error('naturezaId')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Fonte</label>
                            <select wire:model="fonteId"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Selecione...</option>
                                @foreach ($fontes as $f)
                                    <option value="{{ $f->id }}">{{ $f->codigo }} — {{ $f->descricao }}</option>
                                @endforeach
                            </select>
                            @error('fonteId')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Detalhamento</label>
                            <input type="text" wire:model="detalhamento"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Valor (R$)</label>
                            <input type="text" inputmode="numeric"
                                x-data="{
                                    display: '',
                                    format(value) {
                                        const amount = Number(value || 0);
                                        return amount > 0 ? amount.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }) : '';
                                    },
                                    update(value) {
                                        const digits = String(value).replace(/\D/g, '').slice(0, 12);
                                        const amount = digits ? parseInt(digits, 10) : 0;
                                        this.display = this.format(amount);
                                        $wire.set('valor', amount, false);
                                    },
                                }"
                                x-init="display = format($wire.valor)"
                                x-model="display"
                                x-on:input="update($event.target.value)"
                                placeholder="R$ 0,00"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @error('valor')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Observação</label>
                            <textarea wire:model="observacao" rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                        </div>
                        @error('selectedLoaAcaoId')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" wire:click="closeAddLine"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
