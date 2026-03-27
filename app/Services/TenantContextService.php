<?php

namespace App\Services;

use App\Models\TenantUser;
use App\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TenantContextService
{
    protected ?Tenant $resolved = null;

    public function setCurrentTenant(Tenant $tenant): void
    {
        $this->resolved = $tenant;

        // SÓ SALVA SESSÃO SE DISPONÍVEL (MONOLITO/SPA)
        if (request()->hasSession()) {
            Session::put('current_tenant_id', $tenant->id);
        }
    }

    public function currentTenant(): ?Tenant
    {
        // 1. JÁ EM MEMÓRIA
        if ($this->resolved) {
            return $this->resolved;
        }

        // 2. SESSÃO (MONOLITO/SPA)
        if (request()->hasSession()) {
            $id = Session::get('current_tenant_id');
            if ($id) {
                $this->resolved = Tenant::find($id);
                return $this->resolved;
            }
        }

        // 3. TOKEN SANCTUM (API/MOBILE REACT NATIVE)
        $fromToken = $this->resolveFromToken();
        if ($fromToken) {
            $this->resolved = $fromToken;
            return $this->resolved;
        }

        // 4. ÚLTIMO FALLBACK: TENANT DEFAULT DO USUÁRIO
        $this->setDefaultTenantAsCurrent();
        return $this->resolved;
    }

    protected function resolveFromToken(): ?Tenant
    {
        $user = Auth::user();

        if (!$user || !method_exists($user, 'currentAccessToken')) {
            return null;
        }

        $token = $user->currentAccessToken();

        if (!$token || !str_starts_with($token->name, 'tenant_')) {
            return null;
        }

        $tenantId = (int) str_replace('tenant_', '', $token->name);
        return Tenant::find($tenantId);
    }

    public function currentTenantId(): ?int
    {
        return $this->currentTenant()?->id;
    }

    public function setDefaultTenantAsCurrent(): void
    {
        $user = Auth::user();

        if (!$user || !$user->hasDefaultTenant()) {
            return;
        }

        $this->resolved = $user->defaultTenant;

        if (request()->hasSession()) {
            Session::put('current_tenant_id', $this->resolved->id);
        }
    }

    public function switchTo(Tenant $tenant): bool
    {
        $user = Auth::user();

        if (!$user) return false;

        $hasAccess = TenantUser::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->where('status', 'ativo')
            ->exists();

        if (!$hasAccess) return false;

        $this->setCurrentTenant($tenant);
        return true;
    }

    public function currentTenantUser(): ?TenantUser
    {
        $user   = Auth::user();
        $tenant = $this->currentTenant();

        if (!$user || !$tenant) return null;

        return TenantUser::where('user_id', $user->id)
            ->where('tenant_id', $tenant->id)
            ->first();
    }

    public function isCurrentTenantActive(): bool
    {
        $tenant = $this->currentTenant();
        $user   = Auth::user();

        if (!$tenant) return false;

        if ($user?->hasRole('super-admin')) return true;

        return $tenant->subscribed() || $tenant->onTrial();
    }

    public function can(string $permission): bool
    {
        return Auth::user()?->can($permission) ?? false;
    }

    public function clear(): void
    {
        $this->resolved = null;

        if (request()->hasSession()) {
            Session::forget('current_tenant_id');
        }
    }
}
