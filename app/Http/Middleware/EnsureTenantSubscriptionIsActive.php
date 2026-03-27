<?php

namespace App\Http\Middleware;

use App\Services\TenantContextService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantSubscriptionIsActive
{
    public function __construct(
        protected TenantContextService $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->tenantContext->isCurrentTenantActive()) {
            return $this->tenantInactive($request);
        }

        return $next($request);
    }

    private function tenantInactive(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Assinatura inativa. Renove seu plano.',
            ], 403);
        }

        return redirect()->route('billing.inactive');
    }
}
