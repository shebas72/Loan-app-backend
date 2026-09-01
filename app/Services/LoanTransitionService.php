<?php

namespace App\Services;

use App\Enums\LoanStatus;
use App\Models\LoanApplication;
use App\Models\StatusTransition;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LoanTransitionService
{
    public function transition(
        LoanApplication $loan,
        LoanStatus $to,
        User $changedBy,
        ?string $comment = null,
    ): LoanApplication {
        $from = LoanStatus::from($loan->status);

        if (! $from->canTransitionTo($to)) {
            throw new RuntimeException(
                "Cannot transition loan from '{$from->value}' to '{$to->value}'."
            );
        }

        return DB::transaction(function () use ($loan, $from, $to, $changedBy, $comment) {
            $loan->update(['status' => $to->value]);

            StatusTransition::create([
                'tenant_id' => $loan->tenant_id,
                'loan_application_id' => $loan->id,
                'from_status' => $from->value,
                'to_status' => $to->value,
                'changed_by' => $changedBy->id,
                'comment' => $comment,
            ]);

            return $loan->fresh();
        });
    }
}