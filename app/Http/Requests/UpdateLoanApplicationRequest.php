<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['sometimes', 'numeric', 'min:100', 'max:10000000'],
            'purpose' => ['sometimes', 'string', 'max:500'],
        ];
    }
}