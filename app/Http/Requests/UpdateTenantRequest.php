<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by TenantPolicy in the controller
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'support_email' => ['nullable', 'email', 'max:191'],
        ];
    }
}