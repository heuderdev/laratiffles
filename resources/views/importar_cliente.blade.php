<x-adminlte-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestão de Clientes') }}
        </h2>
    </x-slot>
    <div class="col-md-12">
        <div class="d-flex justify-content-start mb-3 gap-2">
            <livewire:clientes.formulario-importar-cliente />
            <livewire:clientes.formulario-cadastrar-cliente-manualmente />
        </div>
        <livewire:clientes.table-lista-cliente-cadastrado />
    </div>
</x-adminlte-layout>