<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceService
{
    public function __construct()
    {
        //
    }

    public function createInvoiceFromCnabData(array $cnabData): void
    {
        try {
            $tenantId = app('currentTenant');

            Invoice::query()->create([
                'tenant_id' => $tenantId,
                'client_id' => $cnabData['client_id'],
                'bank_account_id' => $cnabData['bank_account_id'],
                'source_system' => $cnabData['source_system'],
                'due_date' => $cnabData['due_date'],
                'amount_cents' => $cnabData['amount_cents'],
                'numero_documento' => $cnabData['numero_documento'],
                'nosso_numero' => $cnabData['nosso_numero'],
            ]);
        } catch (\Throwable $th) {
            throw $th; // Re-throw para tratamento em camadas superiores, se necessário
        }
    }
}
