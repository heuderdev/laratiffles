<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportadorClienteFormRequest;
use App\Http\Requests\ImportadorClienteRequest;
use App\Imports\ClientesImport;
use App\Models\Client;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportadorClienteController extends Controller
{
    public function index()
    {
        $clients = Client::where('tenant_id', auth()->user()->default_tenant_id)->get();
        return view('importar_cliente', ['clients' => $clients]);
    }

    public function handleForm(ImportadorClienteFormRequest $request)
    {
        $input = $request->validated();
        Client::create([
            'tenant_id' => auth()->user()->default_tenant_id,
            'name' => $input['name'],
            'email' => $input['email'],
            'whatsapp' => $input['whatsapp'],
            'document' => $input['document'],
            'is_active' => $input['is_active'] ?? true,
        ]);

        return redirect()
            ->route('importar_clientes.index')
            ->with('cliente_cadastrado_sucesso', 'Cliente cadastrado com sucesso!');
    }

    public function handle(ImportadorClienteRequest $request)
    {
        try {
            $request->validated();
            Excel::import(
                new ClientesImport(auth()->user()->default_tenant_id),
                $request->file('file')
            );

            return redirect()
                ->route('importar_clientes.index')
                ->with('success', 'Clientes importados com sucesso!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            $message = "Erros encontrados:\n";
            foreach ($failures as $failure) {
                $message .= sprintf(
                    "Linha %d - %s: %s\n",
                    $failure->row(),
                    $failure->attribute(),
                    $failure->errors()[0]
                );
            }

            return redirect()
                ->route('importar_clientes.index')
                ->with('error', $message);
        } catch (\Exception $e) {
            return redirect()
                ->route('importar_clientes.index')
                ->with('error', 'Erro inesperado: ' . $e->getMessage());
        }
    }
}