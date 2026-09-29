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
use App\Http\Requests\UpdateTenantRequest;
use App\Http\Resources\StaffResource;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\PersonalAccessToken;

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

public function adminShow(Tenant $tenant)
{
    $this->authorize('view', $tenant);

    $tenant->loadCount('users');

    $usersByRole = $tenant->users()
        ->selectRaw('role, count(*) as count')
        ->groupBy('role')
        ->pluck('count', 'role');

    $loansByStatus = $tenant->loanApplications()
        ->selectRaw('status, count(*) as count')
        ->groupBy('status')
        ->pluck('count', 'status');

    $staff = $tenant->users()
        ->whereIn('role', ['bank_admin', 'loan_officer'])
        ->orderBy('role')
        ->orderBy('name')
        ->get();

    return response()->json([
        'data' => new TenantAdminResource($tenant),
        'metrics' => [
            'users_by_role' => $usersByRole,
            'loans_by_status' => $loansByStatus,
            'total_loans' => $tenant->loanApplications()->count(),
        ],
        'staff' => StaffResource::collection($staff),
    ]);
}

public function update(UpdateTenantRequest $request, Tenant $tenant)
{
    $this->authorize('update', $tenant);

    $tenant->update($request->validated());

    return new TenantAdminResource($tenant->loadCount('users'));
}

public function updateStatus(Request $request, Tenant $tenant)
{
    $this->authorize('update', $tenant);

    $validated = $request->validate(['is_active' => ['required', 'boolean']]);

    $tenant->update(['is_active' => $validated['is_active']]);

    if (! $tenant->is_active) {
        PersonalAccessToken::where('tokenable_type', User::class)
            ->whereIn('tokenable_id', $tenant->users()->pluck('id'))
            ->delete();
    }

    return new TenantAdminResource($tenant->loadCount('users'));
}

public function destroy(Tenant $tenant)
{
    $this->authorize('delete', $tenant);

    if ($tenant->loanApplications()->exists()) {
        return response()->json([
            'message' => 'This bank has loan applications on record and cannot be deleted. Suspend it instead.',
        ], 422);
    }

    DB::transaction(function () use ($tenant) {
        $tenant->users()->delete();
        $tenant->delete();
    });

    return response()->noContent();
}

public function resetUserPassword(Request $request, Tenant $tenant, User $user)
{
    $this->authorize('update', $tenant);

    if ($user->tenant_id !== $tenant->id) {
        abort(404);
    }

    $validated = $request->validate([
        'password' => ['required', 'confirmed', Password::min(8)],
    ]);

    $user->update(['password' => $validated['password']]);
    $user->tokens()->delete();

    return response()->json(['message' => "Password reset for {$user->name}."]);
}
}