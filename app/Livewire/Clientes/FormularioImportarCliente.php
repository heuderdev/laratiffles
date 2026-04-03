<?php

namespace App\Livewire\Clientes;

use App\Imports\ClientesImport;
use Livewire\Component;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class FormularioImportarCliente extends Component
{
    use WithFileUploads;

    public $file;

    public function abrirModalImportacao(): void
    {
        $this->resetFormulario();
        $this->dispatch('abrir-modal-importacao-cliente');
    }

    public function fecharModalImportacao(): void
    {
        $this->resetFormulario();
        $this->dispatch('fechar-modal-importacao-cliente');
    }

    public function importarClientes(): void
    {
        $this->validate([
            'file' => [
                'required',
                'file',
                'max:51200',
                'mimes:csv,txt,xls,xlsx',
            ],
        ], $this->messages(), $this->validationAttributes());

        try {
            Excel::import(new ClientesImport(auth()->user()->default_tenant_id), $this->file);

            $this->resetFormulario();

            session()->flash('success', 'Clientes importados com sucesso.');

            $this->dispatch('clientes-importados');
            $this->dispatch('fechar-modal-importacao-cliente');
        } catch (ValidationException $e) {
            $mensagens = [];

            foreach ($e->failures() as $failure) {
                $mensagens[] = 'Linha ' . $failure->row() . ': ' . implode(', ', $failure->errors());
            }

            $this->addError('file', implode(' | ', $mensagens));
        } catch (\Throwable $e) {
            $this->addError('file', 'Ocorreu um erro ao importar o arquivo. Tente novamente.');
        }
    }

    private function resetFormulario(): void
    {
        $this->reset(['file']);
        $this->resetValidation();
    }

    protected function messages(): array
    {
        return [
            'file.required' => 'O arquivo é obrigatório.',
            'file.file' => 'O campo arquivo deve conter um arquivo válido.',
            'file.max' => 'O arquivo deve ter no máximo 50 MB.',
            'file.mimes' => 'O arquivo deve ser do tipo CSV, TXT, XLS ou XLSX.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'file' => 'arquivo',
        ];
    }

    public function render()
    {
        return view('livewire.clientes.formulario-importar-cliente');
    }
}
