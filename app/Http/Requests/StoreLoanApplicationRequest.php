<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // policy check happens in the controller
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:100', 'max:10000000'],
            'purpose' => ['required', 'string', 'max:500'],
        ];
    }
}