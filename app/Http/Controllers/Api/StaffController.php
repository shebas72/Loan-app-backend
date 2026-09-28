<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manageStaff', User::class);

        $staff = User::where('tenant_id', $request->user()->tenant_id)
            ->where('role', 'loan_officer')
            ->orderBy('name')
            ->get();

        return StaffResource::collection($staff);
    }

    public function store(StoreStaffRequest $request)
    {
        $this->authorize('manageStaff', User::class);

        $staff = User::create([
            'tenant_id' => $request->user()->tenant_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => 'loan_officer',
        ]);

        return (new StaffResource($staff))->response()->setStatusCode(201);
    }

    public function update(Request $request, User $staff)
    {
        $this->authorize('manageStaff', User::class);
        $staff = $this->loanOfficerForTenant($request, $staff);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($staff->id)],
        ]);

        $staff->update($validated);

        return new StaffResource($staff->fresh());
    }

    public function updateStatus(Request $request, User $staff)
    {
        $this->authorize('manageStaff', User::class);
        $staff = $this->loanOfficerForTenant($request, $staff);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $staff->update(['is_active' => $validated['is_active']]);

        if (! $staff->is_active) {
            $staff->tokens()->delete();
        }

        return new StaffResource($staff->fresh());
    }

    public function destroy(Request $request, User $staff)
    {
        $this->authorize('manageStaff', User::class);
        $staff = $this->loanOfficerForTenant($request, $staff);
        $staff->delete();

        return response()->noContent();
    }

    private function loanOfficerForTenant(Request $request, User $staff): User
    {
        return User::query()
            ->whereKey($staff->id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('role', 'loan_officer')
            ->firstOrFail();
    }
}