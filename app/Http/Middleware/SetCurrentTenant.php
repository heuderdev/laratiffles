<?php

namespace App\Http\Middleware;

use App\Services\TenantContextService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentTenant
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $tenant = $this->tenantContext->currentTenant();

        if (!$tenant) {
            return $this->tenantNotFound($request);
        }

        return $next($request);
    }

    private function tenantNotFound(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Nenhum tenant encontrado para este usuário.',
            ], 400);
        }

        return redirect()->route('tenant.select');
    }
}
