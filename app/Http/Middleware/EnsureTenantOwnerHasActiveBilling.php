<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantOwnerHasActiveBilling
{
    /**     
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        /** @var Tenant|null $tenant */
        $tenant = $user->defaultTenant;

        abort_unless($tenant instanceof Tenant, 404, 'Tenant não encontrado.');

        if (! $tenant->isOwner($user)) {
            return $next($request);
        }

        if ($tenant->isPaidOrFree()) {
            return $next($request);
        }

        if ($request->routeIs(
            'checkout',
            'portal',
            'billing.success',
            'billing.cancel'
        )) {
            return $next($request);
        }

        return redirect()->route('checkout');
    }
}
