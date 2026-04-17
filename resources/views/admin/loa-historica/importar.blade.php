<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Importar LOA Histórica</h2>
            <a href="{{ route('admin.loa-historica.consultar') }}"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                &larr; Consultar LOAs Anteriores
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <livewire:admin.importar-loa-historica />
            </div>
        </div>
    </div>
</x-app-layout>
