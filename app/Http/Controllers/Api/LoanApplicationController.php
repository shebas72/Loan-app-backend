<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoanApplicationRequest;
use App\Http\Requests\UpdateLoanApplicationRequest;
use App\Http\Resources\LoanApplicationResource;
use App\Models\LoanApplication;
use Illuminate\Http\Request;
use App\Enums\LoanStatus;
use App\Http\Requests\TransitionLoanApplicationRequest;
use App\Services\LoanTransitionService;

class LoanApplicationController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', LoanApplication::class);

        $query = LoanApplication::withCount('documents')->with('applicant', 'assignee');

    if ($request->user()->isRole('applicant')) {
        $query->where('applicant_id', $request->user()->id);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $loans = $query->latest()->paginate(15);

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

        return new LoanApplicationResource(
    $loanApplication->load('applicant', 'assignee', 'documents', 'statusTransitions.changedBy')
);
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
   
   public function transition(
    TransitionLoanApplicationRequest $request,
    LoanApplication $loan_application,
    LoanTransitionService $service,
) {
    $targetStatus = LoanStatus::from($request->to_status);

    if ($targetStatus === LoanStatus::Appealed) {
        $this->authorize('appeal', $loan_application);
    } else {
        $this->authorize('update', $loan_application);
    }

    try {
        $loan = $service->transition($loan_application, $targetStatus, $request->user(), $request->comment);
    } catch (\RuntimeException $e) {
        return response()->json(['message' => $e->getMessage()], 422);
    }

    return new LoanApplicationResource($loan->load('applicant', 'assignee'));
}
}