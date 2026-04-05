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
        Invoice::query()->firstOrCreate(
            [
                'tenant_id' => $cnabData['tenant_id'],
                'bank_account_id' => $cnabData['bank_account_id'],
                'nosso_numero' => $cnabData['nosso_numero'],
            ],
            [
                'client_id' => $cnabData['client_id'],
                'source_system' => $cnabData['source_system'],
                'due_date' => $cnabData['due_date'],
                'amount_cents' => $cnabData['amount_cents'],
                'numero_documento' => $cnabData['numero_documento'],
            ]
        );
    }
}
