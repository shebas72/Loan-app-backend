<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Tenant;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Tenant $tenant): bool
{
    return $user->role === 'admin';
}

public function update(User $user, Tenant $tenant): bool
{
    return $user->role === 'admin';
}

public function delete(User $user, Tenant $tenant): bool
{
    return $user->role === 'admin';
}
}