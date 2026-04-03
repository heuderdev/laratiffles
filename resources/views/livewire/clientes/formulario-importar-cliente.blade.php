<div>
    <button type="button" class="btn btn-success" wire:click="abrirModalImportacao">
        <i class="fas fa-file-import me-1"></i>
        Importar clientes
    </button>

    <div wire:ignore.self class="modal fade" id="modalImportarClientes" tabindex="-1"
        aria-labelledby="modalImportarClientesLabel" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalImportarClientesLabel">
                        Importar clientes por arquivo
                    </h5>
                    <button type="button" class="btn-close" aria-label="Close"
                        wire:click="fecharModalImportacao"></button>
                </div>

                <form wire:submit.prevent="importarClientes">
                    <div class="modal-body">
                        @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                        @endif

                        <div class="mb-3">
                            <label for="file" class="form-label">Arquivo CSV/Excel</label>
                            <input required type="file" id="file"
                                class="form-control @error('file') is-invalid @enderror" wire:model="file"
                                accept=".csv,.txt,.xls,.xlsx">

                            @error('file')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div wire:loading wire:target="file" class="text-muted small">
                            Enviando arquivo...
                        </div>

                        <div wire:loading wire:target="importarClientes" class="alert alert-info mb-0">
                            Processando importação, aguarde...
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="fecharModalImportacao">
                            Fechar
                        </button>

                        <button type="submit" class="btn btn-success" wire:loading.attr="disabled"
                            wire:target="importarClientes,file">
                            <i class="fas fa-file-import me-1"></i>
                            Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
    <script>
        const modalImportarClientesElement = document.getElementById('modalImportarClientes');
    const modalImportarClientesInstance = new bootstrap.Modal(modalImportarClientesElement);

    $wire.on('abrir-modal-importacao-cliente', () => {
        modalImportarClientesInstance.show();
    });

    $wire.on('fechar-modal-importacao-cliente', () => {
        modalImportarClientesInstance.hide();
    });
    </script>
    @endscript
</div>