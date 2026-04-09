<?php

namespace App\Livewire\Cnab\Itau;

use App\Models\Client;
use App\Services\InvoiceService;
use App\Services\Itau\Cnab240Cnab400ItauParserService;
use App\Services\Itau\ItauParserCnab400Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Cnab400 extends Component
{
    use WithFileUploads;

    public $arquivoRem;

    public function abrirModalEnviarRemessa(): void
    {
        $this->resetFormulario();
        $this->dispatch('abrir-modal-enviar-remessa');
    }

    public function fecharModalEnviarRemessa(): void
    {
        $this->resetFormulario();
        $this->dispatch('fechar-modal-enviar-remessa');
    }

    public function enviarRemessa(
        Cnab240Cnab400ItauParserService $detectorService,
        ItauParserCnab400Service $parserService,
        InvoiceService $invoiceService
    ): void {
        $this->validate(
            [
                'arquivoRem' => [
                    'bail',
                    'required',
                    'file',
                    'max:10240',
                    'extensions:rem,txt',
                ],
            ],
            $this->messages(),
            $this->validationAttributes()
        );

        $tenantId = auth()->user()?->default_tenant_id;

        if (!$tenantId) {
            $this->addError('arquivoRem', 'Não foi possível identificar o tenant padrão do usuário.');
            return;
        }

        $nomeOriginal = $this->arquivoRem->getClientOriginalName();
        $nomeSeguro = now()->format('Ymd_His') . '_' . preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $nomeOriginal);

        $caminho = $this->arquivoRem->storeAs(
            'cnab/remessas/itau',
            $nomeSeguro,
            'local'
        );

        try {
            $conteudo = Storage::disk('local')->get($caminho);

            if (trim($conteudo) === '') {
                $this->addError('arquivoRem', 'O arquivo enviado está vazio.');
                return;
            }

            $banco = $detectorService->detectarBanco($conteudo);
            //dd($banco);
            // ITAU 400
            if ((int)$banco["codigo"] === 341 && Str::contains($banco['formato'] ?? '', '400')) {
                $registros = $parserService->parseRegistrosCliente($conteudo);
            }

            if (empty($registros)) {
                $this->addError('arquivoRem', 'Nenhum registro válido foi encontrado no arquivo.');
                return;
            }

            $inputSourceSystem = Str::upper(
                Str::trim(($banco['nome'] ?? '') . '[' . ($banco['formato'] ?? '') . ']')
            );

            $documentos = collect($registros)
                ->pluck('doc_cliente')
                ->filter()
                ->map(fn($documento) => preg_replace('/\D+/', '', (string) $documento))
                ->filter()
                ->unique()
                ->values();

            $clientesPorDocumento = Client::query()
                ->select(['id', 'document'])
                ->whereIn('document', $documentos)
                ->get()
                ->mapWithKeys(function (Client $client) {
                    return [
                        preg_replace('/\D+/', '', (string) $client->document) => $client->id,
                    ];
                });

            $invoicesMap = collect($registros)
                ->map(function (array $value) use ($clientesPorDocumento, $banco, $inputSourceSystem, $tenantId) {
                    $documentoNormalizado = preg_replace('/\D+/', '', (string) ($value['doc_cliente'] ?? ''));
                    $nossoNumero = Str::trim((string) ($value['nosso_numero'] ?? ''));

                    return [
                        'tenant_id'        => $tenantId,
                        'client_id'        => $clientesPorDocumento->get($documentoNormalizado),
                        'bank_account_id'  => $banco['id'] ?? null,
                        'source_system'    => $inputSourceSystem,
                        'due_date'         => $value['vencimento'] ?? null,
                        'amount_cents'     => (int) ($value['valor'] ?? 0),
                        'numero_documento' => $nossoNumero,
                        'nosso_numero'     => $nossoNumero,
                    ];
                })
                ->all();

            DB::transaction(function () use ($invoicesMap, $invoiceService) {
                foreach ($invoicesMap as $data) {
                    $invoiceService->createInvoiceFromCnabData($data);
                }
            });

            $this->resetFormulario();
            $this->dispatch('remessa-enviada');
            $this->dispatch('fechar-modal-enviar-remessa');
        } catch (\Throwable $e) {
            report($e);
            $this->addError('arquivoRem', 'Ocorreu um erro ao processar o arquivo: ' . $e->getMessage());
        } finally {
            if (!empty($caminho) && Storage::disk('local')->exists($caminho)) {
                Storage::disk('local')->delete($caminho);
            }
        }
    }

    private function resetFormulario(): void
    {
        $this->reset(['arquivoRem']);
        $this->resetValidation();
    }

    protected function messages(): array
    {
        return [
            'arquivoRem.required' => 'Selecione o arquivo de remessa.',
            'arquivoRem.file' => 'O arquivo enviado é inválido.',
            'arquivoRem.extensions' => 'Envie um arquivo com extensão .rem ou .txt.',
            'arquivoRem.max' => 'O arquivo não pode ultrapassar 10MB.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'arquivoRem' => 'arquivo',
        ];
    }

    public function render()
    {
        return view('livewire.cnab.itau.cnab400');
    }
}
