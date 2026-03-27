<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

class Tenant extends Model
{
    use HasFactory, Billable;

    protected $guarded = ['id'];

    protected $casts = [
        'credits_balance' => 'decimal:2',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'is_free' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    public function owners()
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot(['role'])
            ->withTimestamps()
            ->wherePivot('role', 'owner');
    }

    public function members()
    {
        return $this->hasMany(TenantUser::class, 'tenant_id');
    }

    public function stripeName(): ?string
    {
        return $this->name;
    }

    public function stripeEmail(): ?string
    {
        return $this->owners()->first()?->email
            ?? $this->users()->orderByPivot('created_at')->first()?->email;
    }

    public function isOwner(User $user): bool
    {
        return $this->users()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'owner')
            ->exists();
    }

    public function isSubscribed(): bool
    {
        return $this->subscribed('default') || $this->onTrial('default');
    }

    public function isPaidOrFree(): bool
    {
        if ($this->is_free) {
            return true;
        }

        if ($this->trial_ends_at instanceof CarbonInterface && $this->trial_ends_at->isFuture()) {
            return true;
        }

        if ($this->isSubscribed()) {
            return true;
        }

        return false;
    }

    public function onGracePeriod(): bool
    {
        return $this->subscription('default')?->onGracePeriod() ?? false;
    }

    public function hasActiveBilling(): bool
    {
        $subscription = $this->subscription('default');

        return $this->subscribed('default')
            || $this->onTrial('default')
            || ($subscription && $subscription->onGracePeriod());
    }
}
