<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Cashier\Billable;

class Tenant extends Model
{
    use HasFactory, Billable;

    protected $guarded = ['id'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    // Se você quiser uma lista de membros via pivot:
    public function members()
    {
        return $this->hasMany(TenantUser::class, 'tenant_id');
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
        return $this->subscribed() || $this->onTrial();
    }

    public function isPaidOrFree(): bool
    {

        if ($this->is_free) {
            return true;
        }

        if ($this->trial_ends_at instanceof Carbon && $this->trial_ends_at->isFuture()) {
            return true;
        }

        if ($this->isSubscribed()) {
            return true;
        }

        return false;
    }

    protected $casts = [
        'credits_balance' => 'decimal:2',
    ];
}
