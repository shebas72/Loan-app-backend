<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('manageStaff', User::class);

        $staff = User::where('tenant_id', $request->user()->tenant_id)
            ->whereIn('role', ['loan_officer', 'bank_admin'])
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
}