<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'default_tenant_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_user')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function defaultTenant()
    {
        return $this->belongsTo(Tenant::class, 'default_tenant_id');
    }

    public function hasDefaultTenant(): bool
    {
        return !is_null($this->default_tenant_id);
    }

    public function setDefaultTenant(Tenant $tenant): void
    {
        $this->update(['default_tenant_id' => $tenant->id]);
    }


    public function isOwnerOfDefaultTenant(): bool
    {
        if (! $this->default_tenant_id) {
            return false;
        }

        return TenantUser::query()
            ->where('tenant_id', $this->default_tenant_id)
            ->where('user_id', $this->id)
            ->where('role', 'owner')
            ->exists();
    }
}
