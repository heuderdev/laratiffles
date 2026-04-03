<?php

namespace App\Livewire\Clientes;

use App\Models\Client;
use App\Rules\CpfOuCnpjValido;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class FormularioCadastrarClienteManualmente extends Component
{
    public ?int $clienteId = null;
    public string $name = '';
    public string $email = '';
    public string $whatsapp = '';
    public string $document = '';
    public bool $is_active = true;

    #[On('editar-cliente')]
    public function abrirModalEditar(int $id): void
    {
        $client = Client::findOrFail($id);

        $this->clienteId = $client->id;
        $this->name = $client->name;
        $this->email = $client->email;
        $this->whatsapp = $client->whatsapp;
        $this->document = $client->document;
        $this->is_active = (bool) $client->is_active;

        $this->dispatch('abrir-modal-cliente');
    }

    public function abrirModal(): void
    {
        $this->resetFormulario();
        $this->dispatch('abrir-modal-cliente');
    }

    public function fecharModal(): void
    {
        $this->resetFormulario();
        $this->dispatch('fechar-modal-cliente');
    }

    private function normalizarCampos(): void
    {
        $this->name = trim($this->name);
        $this->email = mb_strtolower(trim($this->email));
        $this->whatsapp = preg_replace('/\D+/', '', $this->whatsapp);
        $this->document = preg_replace('/\D+/', '', $this->document);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->ignore($this->clienteId),
            ],
            'whatsapp' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('clients', 'whatsapp')->ignore($this->clienteId),
            ],
            'document' => [
                'nullable',
                'string',
                'max:20',
                new CpfOuCnpjValido(),
                Rule::unique('clients', 'document')->ignore($this->clienteId),
            ],
            'is_active' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'O campo nome é obrigatório.',
            'name.string' => 'O campo nome deve ser um texto válido.',
            'name.max' => 'O campo nome deve ter no máximo :max caracteres.',

            'email.required' => 'O campo e-mail é obrigatório.',
            'email.string' => 'O campo e-mail deve ser um texto válido.',
            'email.email' => 'Informe um endereço de e-mail válido.',
            'email.max' => 'O campo e-mail deve ter no máximo :max caracteres.',
            'email.unique' => 'Já existe um cliente cadastrado com este e-mail.',

            'whatsapp.string' => 'O campo WhatsApp deve ser um texto válido.',
            'whatsapp.max' => 'O campo WhatsApp deve ter no máximo :max caracteres.',
            'whatsapp.unique' => 'Já existe um cliente cadastrado com este WhatsApp.',

            'document.string' => 'O campo CPF/CNPJ deve ser um texto válido.',
            'document.max' => 'O campo CPF/CNPJ deve ter no máximo :max caracteres.',
            'document.unique' => 'Já existe um cliente cadastrado com este CPF/CNPJ.',

            'is_active.boolean' => 'O campo status deve ser verdadeiro ou falso.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'whatsapp' => 'WhatsApp',
            'document' => 'CPF/CNPJ',
            'is_active' => 'status',
        ];
    }

    public function cadastrarCliente(): void
    {
        $this->normalizarCampos();

        $validated = $this->validate();

        $tenantId = auth()->user()->default_tenant_id;

        if ($this->clienteId) {
            $client = Client::findOrFail($this->clienteId);
            $client->update($validated);

            session()->flash('success', 'Cliente atualizado com sucesso.');
        } else {
            Client::create([
                ...$validated,
                'tenant_id' => $tenantId,
            ]);

            session()->flash('success', 'Cliente cadastrado com sucesso.');
        }

        $this->dispatch('cliente-cadastrado');
        $this->fecharModal();
    }

    protected function resetFormulario(): void
    {
        $this->reset([
            'clienteId',
            'name',
            'email',
            'whatsapp',
            'document',
        ]);

        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.clientes.formulario-cadastrar-cliente-manualmente');
    }
}
