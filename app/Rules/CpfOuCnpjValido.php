<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CpfOuCnpjValido implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $document = preg_replace('/\D+/', '', (string) $value);

        if ($document === '') {
            $fail('O campo :attribute é obrigatório.');
            return;
        }

        if (! in_array(strlen($document), [11, 14], true)) {
            $fail('O campo :attribute deve conter um CPF ou CNPJ válido.');
            return;
        }

        if (strlen($document) === 11 && ! $this->isCpfValido($document)) {
            $fail('O CPF informado é inválido.');
            return;
        }

        if (strlen($document) === 14 && ! $this->isCnpjValido($document)) {
            $fail('O CNPJ informado é inválido.');
            return;
        }
    }

    private function isCpfValido(string $cpf): bool
    {
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;

            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }

            $digit = ((10 * $sum) % 11) % 10;

            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    private function isCnpjValido(string $cnpj): bool
    {
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        $firstWeights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $secondWeights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $firstWeights[$i];
        }

        $remainder = $sum % 11;
        $firstDigit = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cnpj[12] !== $firstDigit) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $secondWeights[$i];
        }

        $remainder = $sum % 11;
        $secondDigit = $remainder < 2 ? 0 : 11 - $remainder;

        return (int) $cnpj[13] === $secondDigit;
    }
}