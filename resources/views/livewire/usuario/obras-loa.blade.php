@php
    use App\Enums\LoaAcaoStatus;

    $statusDot = fn (LoaAcaoStatus $s) => match ($s) {
        LoaAcaoStatus::Ativa => 'bg-green-500',
        LoaAcaoStatus::Excluida => 'bg-red-500',
        LoaAcaoStatus::Nova => 'bg-blue-500',
        LoaAcaoStatus::Editada => 'bg-amber-400',
    };
    $statusLabel = fn (LoaAcaoStatus $s) => match ($s) {
        LoaAcaoStatus::Ativa => 'Ativa',
        LoaAcaoStatus::Excluida => 'Excluída',
        LoaAcaoStatus::Nova => 'Nova',
        LoaAcaoStatus::Editada => 'Editada',
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
        <div class="bg-orange-50 border border-orange-100 rounded-lg p-4 mb-6">
            <p class="text-sm text-orange-900 font-medium">Obras na LOA</p>
            <p class="text-sm text-orange-800/90 mt-1">
                Cadastre ações do tipo <strong>Obras</strong> por subunidade e lance os valores em
                <a href="{{ route('usuario.loa.preencher', $orcamento) }}" wire:navigate class="underline font-medium">Preencher LOA</a>.
                Para outras classificações, use <a href="{{ route('usuario.loa.metas-acoes', $orcamento) }}" wire:navigate class="underline font-medium">Metas e Ações</a>.
            </p>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Orçamento LOA {{ $orcamento->ano }}</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $unidade->descricao }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('usuario.loa.preencher', $orcamento) }}" wire:navigate
                        class="inline-flex items-center px-4 py-2 bg-white border border-orange-300 rounded-md font-semibold text-xs text-orange-800 uppercase tracking-widest hover:bg-orange-50">
                        Preencher LOA
                    </a>
                    <button type="button" wire:click="openCreateModal"
                        class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 disabled:opacity-50"
                        @disabled(!$subunidadeId)>
                        Nova obra
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
            <label for="sub-obras" class="block text-sm font-medium text-gray-700 mb-2">Subunidade</label>
            <select id="sub-obras" wire:model.live="subunidadeId"
                class="block w-full max-w-md rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 text-sm">
                <option value="">Selecione...</option>
                @foreach ($subunidades as $sub)
                    <option value="{{ $sub->id }}">{{ $sub->codigo }} — {{ $sub->descricao }}</option>
                @endforeach
            </select>
        </div>

        @if ($subunidadeId)
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-600 w-10"> </th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Código</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Nome da obra</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-600">Programa</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Valor lançado</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-600">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($acoes as $acao)
                                @php
                                    $totalObra = (int) ($valorPorAcao[$acao->id] ?? 0);
                                @endphp
                                <tr wire:key="obra-acao-{{ $acao->id }}"
                                    @class(['bg-red-50/60' => $acao->status === LoaAcaoStatus::Excluida])>
                                    <td class="px-4 py-3">
                                        <span class="inline-block w-3 h-3 rounded-full {{ $statusDot($acao->status) }}"
                                            title="{{ $statusLabel($acao->status) }}"></span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700 font-mono">
                                        {{ $acao->acaoOriginal?->codigo ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-900">
                                        <span @class(['line-through text-red-700' => $acao->status === LoaAcaoStatus::Excluida])>
                                            {{ $acao->nome }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        @if ($acao->acaoOriginal?->programa)
                                            {{ $acao->acaoOriginal->programa->codigo }} —
                                            {{ \Illuminate\Support\Str::limit($acao->acaoOriginal->programa->descricao, 40) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-800 font-medium">
                                        R$ {{ number_format($totalObra, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        @if ($acao->status !== LoaAcaoStatus::Excluida)
                                            <button type="button" wire:click="startEdit({{ $acao->id }})"
                                                class="text-orange-700 hover:text-orange-900 text-xs font-medium mr-2">Editar</button>
                                            <button type="button" wire:click="deleteAcao({{ $acao->id }})"
                                                wire:confirm="Marcar esta obra como excluída?"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium mr-2">Excluir</button>
                                        @else
                                            <button type="button" wire:click="restoreAcao({{ $acao->id }})"
                                                class="text-emerald-600 hover:text-emerald-800 text-xs font-medium">Restaurar</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        Nenhuma obra cadastrada para esta subunidade. Use <strong>Nova obra</strong> ou cadastre em Metas e Ações com tipo Obras.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500/75" wire:click="closeCreateModal"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Nova obra</h4>
                    <p class="text-xs text-gray-500 mb-4">A ação será criada com tipo <strong>Obras</strong>.</p>
                    <form wire:submit="saveNewObra" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nome da obra</label>
                            <input type="text" wire:model="newAcaoNome"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                                placeholder="Descrição da obra">
                            @error('newAcaoNome')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @error('subunidadeId')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" wire:click="closeCreateModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700">
                                Criar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if ($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="fixed inset-0 bg-gray-500/75" wire:click="closeEditModal"></div>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Editar obra</h4>
                    <form wire:submit="saveEdit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nome</label>
                            <input type="text" wire:model="editingNome"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                            @error('editingNome')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        @error('editingAcaoId')
                            <p class="text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button" wire:click="closeEditModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
                                Cancelar
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-md hover:bg-orange-700">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
