<?php

namespace App\Services;

use App\Models\TenantUser;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Str;

class TenantService
{
    // Cria tenant default no registro (nome gerado automaticamente)
    public function createDefault(User $user): Tenant
    {
        $name   = $this->generateUniqueName($user->name);
        $tenant = Tenant::create([
            'name' => $name,
            'slug' => Str::slug($name),
        ]);

        $this->attachAdmin($user, $tenant);
        return $tenant;
    }

    // Cria tenant adicional (usuário escolhe o nome)
    public function create(User $user, string $name): Tenant
    {
        $tenant = Tenant::create([
            'name' => $name,
            'slug' => $this->generateUniqueSlug($name),
        ]);

        $this->attachAdmin($user, $tenant);

        $user->setDefaultTenant($tenant);

        if (!$user->hasRole('owner')) {
            $user->assignRole('owner');
        }

        return $tenant;
    }

    // Lista todos os tenants do usuário
    public function listByUser(User $user)
    {
        return Tenant::whereHas('members', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('status', 'ativo');
        })->get();
    }

    // Cria TenantUser como admin no tenant
    private function attachAdmin(User $user, Tenant $tenant): void
    {
        TenantUser::create([
            'user_id'   => $user->id,
            'tenant_id' => $tenant->id,
            'type'      => 'owner',
            'status'    => 'ativo',
        ]);
    }

    // Gera nome único a partir do nome do usuário (registro)
    public function generateUniqueName(string $userName): string
    {
        $base  = Str::slug($userName);
        $name  = $base;
        $count = 2;

        while (Tenant::where('slug', Str::slug($name))->exists()) {
            $name = "{$base}-{$count}";
            $count++;
        }

        return $name;
    }

    // Gera slug único a partir de nome customizado
    public function generateUniqueSlug(string $name): string
    {
        $base  = Str::slug($name);
        $slug  = $base;
        $count = 2;

        while (Tenant::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
