<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantOwnerAndPaid
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->default_tenant_id) {

            return $next($request);
        }

        /** @var Tenant|null $tenant */
        $tenant = Tenant::find($user->default_tenant_id);

        if (! $tenant) {
            return $next($request);
        }

        if (! $tenant->isOwner($user)) {
            return $next($request);
        }

        if (! $tenant->isPaidOrFree()) {
            // abort(403, 'Tenant não está com pagamento em dia.');
            return redirect()->route('billing.subscription.checkout');
        }

        return $next($request);
    }
}
