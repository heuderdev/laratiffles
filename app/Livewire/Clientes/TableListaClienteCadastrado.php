<?php

namespace App\Livewire\Clientes;

use App\Models\Client;
use Livewire\Component;
use Livewire\WithPagination;

class TableListaClienteCadastrado extends Component
{
    use WithPagination;

    protected $listeners = [
        'cliente-cadastrado' => 'atualizarLista',
        'clientes-importados' => 'atualizarLista',
    ];

    public function editarCliente(int $id): void
    {
        $this->dispatch('editar-cliente', id: $id);
    }

    public function atualizarLista(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $clients = Client::query()
            ->where('tenant_id', auth()->user()->default_tenant_id)
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.clientes.table-lista-cliente-cadastrado', [
            'clients' => $clients,
        ]);
    }
}
