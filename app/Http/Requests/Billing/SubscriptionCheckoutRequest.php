<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'price_id' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'price_id.required' => 'O ID do plano é obrigatório.',
        ];
    }
}
