<?php

namespace App\Http\Requests;

use App\Enums\LoanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class TransitionLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'to_status' => ['required', new Enum(LoanStatus::class)],
            'comment' => ['nullable', 'string', 'max:1000'],
        ];
    }
}