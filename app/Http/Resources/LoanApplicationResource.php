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
            'documents_count' => $this->whenCounted('documents'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
