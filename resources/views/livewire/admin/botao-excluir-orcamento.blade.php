<div class="inline-block">
    <button
        type="button"
        wire:click="excluir"
        wire:confirm="Excluir definitivamente o orçamento {{ $tipo }} {{ $ano }}? Todos os dados vinculados serão removidos."
        class="text-red-600 hover:text-red-800 font-medium text-sm"
    >
        Excluir
    </button>
</div>
