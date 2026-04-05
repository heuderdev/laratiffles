<div>
    <button type="button" class="btn btn-primary" wire:click="abrirModalEnviarRemessa">
        Enviar remessa
    </button>

    <div wire:ignore.self class="modal fade" id="modalEnviarRemessa" tabindex="-1"
        aria-labelledby="modalEnviarRemessaLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEnviarRemessaLabel">
                        Enviar arquivo de remessa
                    </h5>
                    <button type="button" class="btn-close" aria-label="Close"
                        wire:click="fecharModalEnviarRemessa"></button>
                </div>

                <form wire:submit.prevent="enviarRemessa">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="arquivoRem" class="form-label">Arquivo .rem</label>
                            <input required type="file" id="arquivoRem"
                                class="form-control @error('arquivoRem') is-invalid @enderror" wire:model="arquivoRem"
                                accept=".rem,.txt">

                            @error('arquivoRem')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div wire:loading wire:target="arquivoRem" class="text-muted small">
                            Fazendo upload do arquivo...
                        </div>

                        <div wire:loading wire:target="enviarRemessa" class="alert alert-info mb-0 mt-3">
                            Processando arquivo, aguarde...
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="fecharModalEnviarRemessa">
                            Cancelar
                        </button>

                        <button type="submit" class="btn btn-primary" wire:loading.attr="disabled"
                            wire:target="enviarRemessa,arquivoRem">
                            Enviar arquivo
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @script
    <script>
        const modalEnviarRemessaElement = document.getElementById('modalEnviarRemessa');
        const modalEnviarRemessaInstance = new bootstrap.Modal(modalEnviarRemessaElement);

        $wire.on('remessa-enviada', () => {
            swal.fire({
                icon: 'success',
                title: 'Remessa processada',
                text: 'O arquivo .rem foi enviado com sucesso.',
                timer: 3000,
                showConfirmButton: false,
            });
        });

        $wire.on('abrir-modal-enviar-remessa', () => {
            modalEnviarRemessaInstance.show();
        });

        $wire.on('fechar-modal-enviar-remessa', () => {
            modalEnviarRemessaInstance.hide();
        });
    </script>
    @endscript
</div>