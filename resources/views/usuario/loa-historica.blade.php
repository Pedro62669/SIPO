<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Consultar LOAs Anteriores</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto px-4 sm:px-6 lg:px-8" style="max-width: 140rem;">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <livewire:usuario.consultar-loas-historicas />
            </div>
        </div>
    </div>
</x-app-layout>
