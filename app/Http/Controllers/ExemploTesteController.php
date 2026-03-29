<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\Itau\Cnab240Cnab400ItauParserService;
use App\Services\Itau\ItauParserCnab400Service;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExemploTesteController extends Controller
{

    public function simalarUpload()
    {
        $pathRelativo = '1006.rem';
        $conteudo = Storage::disk('local')->get($pathRelativo);
        $banco = app(Cnab240Cnab400ItauParserService::class)->detectarBanco($conteudo);
        $conteudo = app(ItauParserCnab400Service::class)->parseRegistrosCliente($conteudo);
        $input_source_system = Str::trim(Str::upper($banco['nome'] . '[' . $banco['formato'] . ']'));

        $documentos = collect($conteudo)
            ->pluck('doc_cliente')
            ->filter()
            ->map(fn($documento) => preg_replace('/\D+/', '', (string) $documento))
            ->unique()
            ->values();

        $clientesPorDocumento = Client::query()
            ->whereIn('document', $documentos)
            ->pluck('id', 'document');


        $invoicesMap = collect($conteudo)
            ->map(function ($value) use ($clientesPorDocumento, $banco, $input_source_system) {
                $clienteId = $clientesPorDocumento->get(Str::trim($value['doc_cliente'] ?? ''));
                return [
                    'client_id'         => $clienteId,
                    'bank_account_id'   => $banco['id'] ?? null,
                    'source_system'     => $input_source_system,
                    'due_date'          => $value['vencimento'] ?? null,
                    'amount_cents'      => (int) $value['valor'],
                    'numero_documento'  => Str::trim($value['nosso_numero']),
                    'nosso_numero'      => Str::trim($value['nosso_numero']),
                ];
            })
            ->all();
        foreach ($invoicesMap as $data) {
            app(InvoiceService::class)->createInvoiceFromCnabData($data);
        }


        // foreach ($conteudo as  $value) {
        //     $clientId = $clientesPorDocumento[$value['doc_cliente']] ?? null;
        //     app(InvoiceService::class)->createInvoiceFromCnabData([
        //         'client_id' => $clientId,
        //         'bank_account_id' => $banco['id'],
        //         'source_system' => $input_source_system,
        //         'due_date' => $value['vencimento'],
        //         'amount_cents' => $value['valor'],
        //         'numero_documento' => Str::trim($value['nosso_numero']),
        //         'nosso_numero' => Str::trim($value['nosso_numero']),
        //     ]);
        // }
    }
}
