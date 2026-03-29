<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = static::resolveTenantId();
            if ($tenantId !== null) {
                $builder->where($builder->getModel()->getTable() . '.tenant_id', $tenantId);
            }
        });

        static::creating(function (Model $model) {
            if (! isset($model->tenant_id) || empty($model->tenant_id)) {
                $tenantId = static::resolveTenantId();

                if ($tenantId === null) {
                    throw new RuntimeException('Tenant não resolvido para criação do registro.');
                }

                $model->tenant_id = $tenantId;
            }
        });
    }

    public function scopeForCurrentTenant(Builder $query): Builder
    {
        $tenantId = static::resolveTenantId();

        if ($tenantId === null) {
            throw new RuntimeException('Tenant atual não encontrado.');
        }

        return $query->where($this->getTable() . '.tenant_id', $tenantId);
    }

    public function scopeForTenant(Builder $query, int|string $tenantId): Builder
    {
        return $query->where($this->getTable() . '.tenant_id', $tenantId);
    }

    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }

    protected static function resolveTenantId(): int|string|null
    {
        if (app()->bound('currentTenant')) {
            $tenant = app('currentTenant');

            if ($tenant) {
                return $tenant;
            }
        }

        if (auth()->check() && isset(auth()->user()->default_tenant_id)) {
            return auth()->user()->default_tenant_id;
        }

        return request()->route('tenant')
            ?? request()->get('tenant_id')
            ?? null;
    }
}
