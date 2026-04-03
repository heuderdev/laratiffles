<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportadorClienteFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? trim($this->name) : $this->name,
            'email' => is_string($this->email) ? mb_strtolower(trim($this->email)) : $this->email,
            'whatsapp' => is_string($this->whatsapp) ? preg_replace('/\D+/', '', $this->whatsapp) : $this->whatsapp,
            'document' => is_string($this->document) ? preg_replace('/\D+/', '', $this->document) : $this->document,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        $tenantId = auth()->user()->default_tenant_id;

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:150',
            ],

            'email' => [
                'required',
                'string',
                'email:rfc,dns',
                'max:190',
                Rule::unique('clients', 'email')
                    ->where(fn($query) => $query->where('tenant_id', $tenantId)),
            ],

            'whatsapp' => [
                'required',
                'string',
                'digits_between:10,20',
                Rule::unique('clients', 'whatsapp')
                    ->where(fn($query) => $query->where('tenant_id', $tenantId)),
            ],

            'document' => [
                'required',
                'string',
                'digits_between:11,20',
                Rule::unique('clients', 'document')
                    ->where(fn($query) => $query->where('tenant_id', $tenantId)),
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'whatsapp' => 'whatsapp',
            'document' => 'CPF/CNPJ',
            'is_active' => 'status',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.min' => 'O nome deve ter no mínimo :min caracteres.',
            'name.max' => 'O nome deve ter no máximo :max caracteres.',

            'email.required' => 'O e-mail é obrigatório.',
            'email.string' => 'O e-mail deve ser um texto válido.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail deve ter no máximo :max caracteres.',
            'email.unique' => 'Já existe um cliente com este e-mail neste tenant.',

            'whatsapp.required' => 'O whatsapp é obrigatório.',
            'whatsapp.string' => 'O whatsapp deve ser um texto válido.',
            'whatsapp.digits_between' => 'O whatsapp deve conter entre :min e :max dígitos.',
            'whatsapp.unique' => 'Já existe um cliente com este whatsapp neste tenant.',

            'document.required' => 'O CPF/CNPJ é obrigatório.',
            'document.string' => 'O CPF/CNPJ deve ser um texto válido.',
            'document.digits_between' => 'O CPF/CNPJ deve conter entre :min e :max dígitos.',
            'document.unique' => 'Já existe um cliente com este CPF/CNPJ neste tenant.',

            'is_active.boolean' => 'O status deve ser verdadeiro ou falso.',
        ];
    }
}
