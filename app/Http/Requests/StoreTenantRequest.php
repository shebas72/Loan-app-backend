<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gated by TenantPolicy in the controller, same pattern as StoreStaffRequest
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'admin_name' => ['required', 'string', 'max:191'],
            'admin_email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'admin_password' => ['required', 'confirmed', Password::min(8)],
        ];
    }
}