<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantUser extends Model
{
    protected $table = 'tenant_user';

    protected $fillable = ['tenant_id', 'user_id', 'role'];

    public function isActive(): bool
    {
        return $this->status === 'ativo';
    }

    public function isAdmin(): bool
    {
        return $this->type === 'owner';
    }

    public function isFuncionario(): bool
    {
        return $this->type === 'funcionario';
    }

    public function isCliente(): bool
    {
        return $this->type === 'cliente';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'ativo');
    }
}
