<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantEmployeeHasActiveTenant
{
    public function handle(Request $request, Closure $next): Response|null
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        /** @var Tenant|null $tenant */
        $tenant = $request->route('tenant') ?? $user->defaultTenant;

        abort_unless($tenant instanceof Tenant, 404, 'Tenant não encontrado.');

        if (! $this->isEmployeeOfTenant($user, $tenant)) {
            return $next($request);
        }
        // dd($tenant->hasActiveBilling(), $tenant->isPaidOrFree());
        if ($tenant->hasActiveBilling() || $tenant->isPaidOrFree()) {
            return $next($request);
        }

        return redirect()
            ->route('dashboard')
            ->with('warning', 'Este tenant está inativo. Contate o responsável para regularizar o pagamento.');
    }

    private function isEmployeeOfTenant(object $user, Tenant $tenant): bool
    {
        return $tenant->users()
            ->where('users.id', $user->id)
            ->wherePivot('status', 'ativo')
            ->wherePivot('role', 'funcionario')
            ->exists();
    }
}
