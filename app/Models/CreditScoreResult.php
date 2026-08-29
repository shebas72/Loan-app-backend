<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Concerns\BelongsToTenant;

class CreditScoreResult extends Model
{
   use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'loan_application_id',
        'score',
        'decision',
        'factors',
    ];

    protected function casts(): array
    {
        return [
            'factors' => 'array',
        ];
    }

    public function loanApplication(): BelongsTo
    {
        return $this->belongsTo(LoanApplication::class);
    }
}