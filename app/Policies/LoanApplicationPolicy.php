<?php

namespace App\Policies;

use App\Models\LoanApplication;
use App\Models\User;

class LoanApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // tenant scope already limits which rows they see
    }

    public function view(User $user, LoanApplication $loan): bool
    {
        return $user->tenant_id === $loan->tenant_id;
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

        // Applicants can only edit their OWN application, and only while still a draft
        if ($user->isRole('applicant')) {
            return $user->id === $loan->applicant_id && $loan->status === 'draft';
        }

        return $user->isRole('loan_officer') || $user->isRole('underwriter');
    }

    public function approve(User $user, LoanApplication $loan): bool
    {
        return $user->tenant_id === $loan->tenant_id
            && ($user->isRole('underwriter') || $user->isRole('branch_manager'));
    }

    public function delete(User $user, LoanApplication $loan): bool
    {
        return $user->tenant_id === $loan->tenant_id && $user->isRole('admin');
    }
}