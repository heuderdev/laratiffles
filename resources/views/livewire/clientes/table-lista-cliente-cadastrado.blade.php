<div>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Nome</th>
                <th>E-mail</th>
                <th>WhatsApp</th>
                <th>CPF/CNPJ</th>
                <th>Status</th>
                <th>Ações</th>
                </th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
            <tr>
                <td>{{ $client->name }}</td>
                <td>{{ $client->email }}</td>
                <td>{{ $client->whatsapp }}</td>
                <td>{{ $client->document }}</td>
                <td>
                    <span class="badge bg-{{ $client->is_active ? 'success' : 'secondary' }}">
                        {{ $client->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary"
                        wire:click="editarCliente({{ $client->id }})">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center text-muted">
                    Nenhum cliente cadastrado.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-2">
        {{ $clients->links() }}
    </div>
</div>