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
    return DB::transaction(function () use ($loan, $to, $changedBy, $comment) {
        // Lock the row and re-read it, so two officers racing to claim
        // the same loan can't both succeed on stale data.
        $loan = LoanApplication::lockForUpdate()->findOrFail($loan->id);
        $from = LoanStatus::from($loan->status);

        if (! $from->canTransitionTo($to)) {
            throw new RuntimeException(
                "Cannot transition loan from '{$from->value}' to '{$to->value}'."
            );
        }

        $updates = ['status' => $to->value];

        // First loan officer to start review claims the case
        if ($to === LoanStatus::UnderReview
            && $loan->assigned_to === null
            && $changedBy->isRole('loan_officer')) {
            $updates['assigned_to'] = $changedBy->id;
        }

        $loan->update($updates);

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