<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Consultar LOAs Anteriores</h2>
            <a href="{{ route('admin.loa-historica.importar') }}"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                + Importar Novo Ano
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto px-4 sm:px-6 lg:px-8" style="max-width: 140rem;">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <livewire:admin.consultar-loas />
            </div>
        </div>
    </div>
</x-app-layout>
