<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportadorClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'tenant_id' => auth()->user()->default_tenant_id,
        ]);
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:csv,txt,xls,xlsx',
            ],

            'tenant_id' => [
                'required',
                'integer',
                'exists:tenants,id',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'file' => 'arquivo',
            'tenant_id' => 'tenant',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'O arquivo é obrigatório.',
            'file.file' => 'O envio deve ser um arquivo válido.',
            'file.max' => 'O arquivo não pode ser maior que 10MB.',
            'file.mimes' => 'O arquivo deve ser do tipo CSV, XLS ou XLSX.',

            'tenant_id.required' => 'O tenant do usuário autenticado é obrigatório.',
            'tenant_id.integer' => 'O tenant do usuário autenticado é inválido.',
            'tenant_id.exists' => 'O tenant do usuário autenticado não foi encontrado.',
        ];
    }
}
