<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TenantOptionResource;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Http\Resources\TenantResource;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::where('is_active', true)
            ->orderBy('name')
            ->get();

        return TenantOptionResource::collection($tenants);
    }

    public function mine(Request $request)
{
    $tenant = $request->user()->tenant;

    if (! $tenant) {
        return response()->json(['message' => 'No associated bank found.'], 404);
    }

    return new TenantResource($tenant);
}
}