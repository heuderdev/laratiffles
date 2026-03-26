<?php

namespace App\Services\Itau;

use DateTime;

class ItauParserCnab400Service
{
    public function __construct() {}

    public function parseRegistrosCliente(string $conteudo): array
    {
        $linhas = preg_split('/\r\n|\r|\n/', $conteudo) ?: [];
        $registros = [];

        foreach ($linhas as $linha) {
            $linha = rtrim($linha, "\r\n");

            if (! $this->isRegistroDetalheValido($linha)) {
                continue;
            }

            $registro = [
                'nosso_numero'   => $this->extrairNossoNumero($linha),
                'tipo_doc'       => $this->extrairTipoDocumento($linha),
                'doc_cliente'    => $this->extrairDocumento($linha),
                'vencimento'     => $this->extrairVencimento($linha),
                'valor'          => $this->extrairValor($linha),
                'linha_completa' => $linha,
            ];

            $registros[] = $registro;
        }

        return $registros;
    }

    private function isRegistroDetalheValido(string $linha): bool
    {
        $tamanho = strlen($linha);

        // Aceita entre 384 e 400 caracteres
        if ($tamanho < 384 || $tamanho > 400) {
            return false;
        }

        // Tipo de registro deve ser "1" (detalhe)
        if (substr($linha, 0, 1) !== '1') {
            return false;
        }

        return true;
    }

    private function extrairNossoNumero(string $linha): string
    {
        // CNAB400 Itaú – ajuste as posições conforme seu manual oficial
        // Posição 63-70 (base 1) => substr(62, 8)
        $nossoNumero = substr($linha, 62, 8);

        return ltrim(trim($nossoNumero), '0');
    }

    private function extrairTipoDocumento(string $linha): string
    {
        // Posição 219 (base 1)
        $tipo = trim(substr($linha, 218, 2));

        return match ($tipo) {
            '01', '1' => 'CPF',
            '02', '2' => 'CNPJ',
            default   => $tipo,
        };
    }

    private function extrairDocumento(string $linha): string
    {
        // Posição 221-234 (base 1) => substr(220, 14)
        $doc = substr($linha, 220, 14);

        return ltrim(trim($doc), '0');
    }

    private function extrairVencimento(string $linha): ?string
    {
        // Posição 121-126 (base 1) => substr(120, 6)
        $valor = trim(substr($linha, 120, 6));

        if ($valor === '' || $valor === '000000') {
            return null;
        }

        $data = DateTime::createFromFormat('dmy', $valor);

        if (! $data) {
            return null;
        }

        return $data->format('Y-m-d');
    }

    private function extrairValor(string $linha): float
    {
        // Posição 127-139 (base 1) => substr(126, 13)
        $valorStr = trim(substr($linha, 126, 13));

        if ($valorStr === '' || ! ctype_digit($valorStr)) {
            return 0.0;
        }

        return ((int) $valorStr) / 100;
    }
}
