<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'id' => $this->id,
            'amount' => $this->amount,
            'purpose' => $this->purpose,
            'status' => $this->status,
            'applicant' => [
                'id' => $this->applicant->id,
                'name' => $this->applicant->name,
                'email' => $this->applicant->email,
            ],
             // NEW: who has claimed this case (null if unclaimed)
            'assignee' => $this->whenLoaded('assignee', fn () => $this->assignee
                ? ['id' => $this->assignee->id, 'name' => $this->assignee->name]
                : null),

            // NEW: can the current user work this loan? Answered by the policy
            'can_transition' => $request->user()?->can('update', $this->resource) ?? false,

            'documents_count' => $this->whenCounted('documents'),
            'status_transitions' => StatusTransitionResource::collection(
            $this->whenLoaded('statusTransitions')
        ),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
