<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTenantRequest;
use App\Http\Resources\TenantAdminResource;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

public function adminIndex(Request $request)
{
    $this->authorize('viewAny', Tenant::class);

    $tenants = Tenant::withCount('users')->orderBy('name')->get();

    return TenantAdminResource::collection($tenants);
}
public function store(StoreTenantRequest $request)
{
    $this->authorize('create', Tenant::class);

    $tenant = DB::transaction(function () use ($request) {
        $tenant = Tenant::create([
            'name' => $request->name,
            'slug' => $this->uniqueSlug($request->name),
            'is_active' => true,
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'name' => $request->admin_name,
            'email' => $request->admin_email,
            'password' => $request->admin_password, // hashed by the model's cast
            'role' => 'bank_admin',
        ]);

        return $tenant;
    });

    return (new TenantAdminResource($tenant->loadCount('users')))
        ->response()
        ->setStatusCode(201);
}

private function uniqueSlug(string $name): string
{
    $base = Str::slug($name);
    $slug = $base;
    $suffix = 1;

    while (Tenant::where('slug', $slug)->exists()) {
        $slug = $base.'-'.++$suffix;
    }

    return $slug;
}
}