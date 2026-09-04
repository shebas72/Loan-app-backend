<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TenantOptionResource;
use App\Models\Tenant;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::where('is_active', true)
            ->orderBy('name')
            ->get();

        return TenantOptionResource::collection($tenants);
    }
}