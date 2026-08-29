<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Concerns\BelongsToTenant;

class LoanApplication extends Model
{
    use HasUuids, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'applicant_id',
        'amount',
        'purpose',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function applicant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function statusTransitions(): HasMany
    {
        return $this->hasMany(StatusTransition::class)->latest();
    }

    public function creditScoreResult(): HasOne
    {
        return $this->hasOne(CreditScoreResult::class);
    }
}