<?php

namespace App\Policies;

use App\Models\LoanApplication;
use App\Models\User;

class LoanApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // index() filters rows by role
    }

    public function view(User $user, LoanApplication $loan): bool
    {
        if ($user->tenant_id !== $loan->tenant_id) {
            return false;
        }

        if ($user->isRole('applicant')) {
            return $user->id === $loan->applicant_id;
        }

        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isRole('applicant');
    }

    public function update(User $user, LoanApplication $loan): bool
    {
        if ($user->tenant_id !== $loan->tenant_id) {
            return false;
        }

        if ($user->isRole('applicant')) {
            return $user->id === $loan->applicant_id && $loan->status === 'draft';
        }

        return $this->canWork($user, $loan);
    }

    public function approve(User $user, LoanApplication $loan): bool
    {
        return $user->tenant_id === $loan->tenant_id && $this->canWork($user, $loan);
    }

    public function appeal(User $user, LoanApplication $loan): bool
    {
        return $user->tenant_id === $loan->tenant_id
            && $user->isRole('applicant')
            && $user->id === $loan->applicant_id
            && $loan->status === 'rejected';
    }

    public function assign(User $user, LoanApplication $loan): bool
    {
        return $user->tenant_id === $loan->tenant_id && $user->isRole('bank_admin');
    }

    public function delete(User $user, LoanApplication $loan): bool
    {
        return $user->tenant_id === $loan->tenant_id && $user->isRole('bank_admin');
    }

    /**
     * Bank admins can always work a case. Loan officers can work it
     * only while it's unclaimed or claimed by them.
     */
    protected function canWork(User $user, LoanApplication $loan): bool
    {
        if ($user->isRole('bank_admin')) {
            return true;
        }

        if ($user->isRole('loan_officer')) {
            return $loan->assigned_to === null || $loan->assigned_to === $user->id;
        }

        return false;
    }
}