<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantAdminResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
             'id' => $this->id,
        'name' => $this->name,
        'slug' => $this->slug,
        'is_active' => $this->is_active,
        'users_count' => $this->users_count,
        'address' => $this->address,
        'phone' => $this->phone,
        'support_email' => $this->support_email,
        'created_at' => $this->created_at,
        ];
    }
}