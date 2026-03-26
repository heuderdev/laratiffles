<?php

namespace App\Services\Itau;

use App\Models\BancoFebraban;

class Cnab240Cnab400ItauParserService
{
    public function detectarBanco(string $conteudo): array
    {
        $linhas = explode("\n", $conteudo);
        $primeiraLinha = trim($linhas[0] ?? '');

        $formato = $this->detectarFormato($primeiraLinha);

        if (strlen($primeiraLinha) < 3) {
            return [
                'id'         => null,
                'codigo'     => null,
                'nome'       => 'Inválido',
                'encontrado' => false,
                'formato'    => $formato,
            ];
        }

        $codigo = substr($primeiraLinha, 76, 3);

        $codigoBanco = $this->extrairCodigoBanco($primeiraLinha, $formato);

        $banco = BancoFebraban::query()
            ->where('codigo', $codigoBanco)
            ->where('ativo', true)
            ->first();

        return [
            'id'         => $banco?->id,
            'codigo'     => $codigo,
            'nome'       => $banco?->nome ?? "Banco desconhecido ({$codigo})",
            'encontrado' => $banco !== null,
            'formato'    => $formato,
        ];
    }

    private function detectarFormato(string $linha): string
    {
        $tamanho = strlen($linha);

        if ($tamanho === 400) {
            return 'cnab400';
        }

        if ($tamanho === 240) {
            return 'cnab240';
        }

        return 'invalido';
    }

    private function extrairCodigoBanco(string $linha, string $formato): string
    {
        if ($formato === 'cnab400') {
            return substr($linha, 76, 3);
        }

        if ($formato === 'cnab240') {
            return substr($linha, 0, 3);
        }

        return '';
    }
}
