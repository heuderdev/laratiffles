<?php

namespace App\Http\Controllers;

use App\Services\Itau\Cnab240Cnab400ItauParserService;
use App\Services\Itau\ItauParserCnab400Service;
use Illuminate\Support\Facades\Storage;

class ExemploTesteController extends Controller
{

    public function simalarUpload()
    {
        $pathRelativo = '1006.rem';
        $conteudo = Storage::disk('local')->get($pathRelativo);
        $banco = app(Cnab240Cnab400ItauParserService::class)->detectarBanco($conteudo);
        $conteudo = app(ItauParserCnab400Service::class)->parseRegistrosCliente($conteudo);
        if ($banco['codigo'] == '341' && $banco['formato'] == 'cnab400') {
            dd($conteudo);
        }
    }
}
