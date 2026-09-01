<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoanApplicationRequest;
use App\Http\Requests\UpdateLoanApplicationRequest;
use App\Http\Resources\LoanApplicationResource;
use App\Models\LoanApplication;
use Illuminate\Http\Request;

class LoanApplicationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', LoanApplication::class);

        $loans = LoanApplication::withCount('documents')
            ->with('applicant')
            ->latest()
            ->paginate(15);

        return LoanApplicationResource::collection($loans);
    }

    public function store(StoreLoanApplicationRequest $request)
    {
        $this->authorize('create', LoanApplication::class);

        $loan = LoanApplication::create([
            'applicant_id' => $request->user()->id,
            'amount' => $request->amount,
            'purpose' => $request->purpose,
        ]);

        return new LoanApplicationResource($loan->load('applicant'));
    }

    public function show(LoanApplication $loanApplication)
    {
        $this->authorize('view', $loanApplication);

        return new LoanApplicationResource($loanApplication->load('applicant', 'documents'));
    }

    public function update(UpdateLoanApplicationRequest $request, LoanApplication $loanApplication)
    {
        $this->authorize('update', $loanApplication);

        $loanApplication->update($request->validated());

        return new LoanApplicationResource($loanApplication->load('applicant'));
    }

    public function destroy(LoanApplication $loanApplication)
    {
        $this->authorize('delete', $loanApplication);

        $loanApplication->delete();

        return response()->json(null, 204);
    }
}