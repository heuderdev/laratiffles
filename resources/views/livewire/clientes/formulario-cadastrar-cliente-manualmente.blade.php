<div>
    <button type="button" class="btn btn-primary mb-4" wire:click="abrirModal">
        <i class="fas fa-user-plus me-1"></i>
        Cadastrar cliente
    </button>

    <div wire:ignore.self class="modal fade" id="modalCadastrarCliente" data-bs-backdrop="static"
        data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalCadastrarClienteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCadastrarClienteLabel">
                        {{ $clienteId ? 'Editar cliente' : 'Cadastrar cliente manualmente' }}
                    </h5>
                    <button type="button" class="btn-close" aria-label="Close" wire:click="fecharModal"></button>
                </div>

                <form wire:submit.prevent="cadastrarCliente">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nome</label>
                                <input type="text" id="name" class="form-control @error('name') is-invalid @enderror"
                                    wire:model.defer="name">
                                @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                    wire:model.defer="email">
                                @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="whatsapp" class="form-label">WhatsApp</label>
                                <input type="text" id="whatsapp"
                                    class="form-control @error('whatsapp') is-invalid @enderror"
                                    wire:model.defer="whatsapp">
                                @error('whatsapp')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="document" class="form-label">CPF/CNPJ</label>
                                <input type="text" id="document"
                                    class="form-control @error('document') is-invalid @enderror"
                                    wire:model.defer="document">
                                @error('document')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active"
                                        wire:model.defer="is_active">
                                    <label class="form-check-label" for="is_active">
                                        Cliente ativo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="fecharModal">
                            Fechar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            {{ $clienteId ? 'Atualizar cliente' : 'Salvar cliente' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
    <script>
        const modalElement = document.getElementById('modalCadastrarCliente');
        const modalInstance = new bootstrap.Modal(modalElement);

        $wire.on('abrir-modal-cliente', () => {
            modalInstance.show();
        });

        $wire.on('fechar-modal-cliente', () => {
            modalInstance.hide();
        });

        modalElement.addEventListener('hidden.bs.modal', () => {
            $wire.call('fecharModal');
        });
    </script>
    @endscript
</div>