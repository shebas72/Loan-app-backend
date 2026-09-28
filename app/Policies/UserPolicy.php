<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function manageStaff(User $user): bool
    {
        return $user->isRole('bank_admin') && $user->tenant_id !== null;
    }
}