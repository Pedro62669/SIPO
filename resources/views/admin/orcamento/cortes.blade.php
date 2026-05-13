<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cortes do Orçamento</h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:admin.cortes-orcamento :orcamento-id="$orcamentoId" />
        </div>
    </div>
</x-app-layout>
