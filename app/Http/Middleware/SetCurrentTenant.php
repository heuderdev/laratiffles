<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentTenant
{
    /**
     * Rotas/padrões que NÃO devem resolver tenant.
     *
     * @var array<int, string>
     */
    protected array $except = [
        'login',
        'logout',
        'register',
        'password/*',
        'sanctum/*',
        'up',
        'horizon/*',
        'telescope/*',
        'admin/*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldIgnore($request)) {
            return $next($request);
        }

        $tenant = auth()->user()->default_tenant_id;
        // dd(auth()->user()->toArray());
        if ($tenant) {
            app()->instance('currentTenant', $tenant);
        }

        return $next($request);
    }

    protected function shouldIgnore(Request $request): bool
    {
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return true;
            }
        }

        return false;
    }
}
