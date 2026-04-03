<?php

namespace App\Imports;

use App\Models\Client;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class ClientesImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithChunkReading,
    WithBatchInserts,
    SkipsOnFailure
{
    use Importable;
    use SkipsFailures;

    public function __construct(
        private readonly int $tenantId
    ) {}

    public function prepareForValidation(array $row, int $index): array
    {
        $row['name'] = isset($row['name']) ? trim((string) $row['name']) : null;
        $row['email'] = isset($row['email']) ? mb_strtolower(trim((string) $row['email'])) : null;
        $row['whatsapp'] = isset($row['whatsapp']) ? preg_replace('/\D+/', '', (string) $row['whatsapp']) : null;
        $row['document'] = isset($row['document']) ? preg_replace('/\D+/', '', (string) $row['document']) : null;

        if (array_key_exists('is_active', $row)) {
            $row['is_active'] = filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return $row;
    }

    public function model(array $row): Client
    {
        return new Client([
            'tenant_id' => $this->tenantId,
            'name' => trim((string) $row['name']),
            'email' => mb_strtolower(trim((string) $row['email'])),
            'whatsapp' => preg_replace('/\D+/', '', (string) $row['whatsapp']),
            'document' => preg_replace('/\D+/', '', (string) $row['document']),
            'is_active' => $row['is_active'] ?? true,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'min:2', 'max:150'],
            'email' => [
                'required',
                'email:rfc,dns',
                'max:190',
                Rule::unique('clients', 'email')
                    ->where(fn($query) => $query->where('tenant_id', $this->tenantId)),
            ],
            'whatsapp' => [
                'required',
                'min:10',
                'max:20',
                Rule::unique('clients', 'whatsapp')
                    ->where(fn($query) => $query->where('tenant_id', $this->tenantId)),
            ],
            'document' => [
                'required',
                'min:11',
                'max:20',
                Rule::unique('clients', 'document')
                    ->where(fn($query) => $query->where('tenant_id', $this->tenantId)),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'whatsapp' => 'whatsapp',
            'document' => 'documento',
            'is_active' => 'status',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
