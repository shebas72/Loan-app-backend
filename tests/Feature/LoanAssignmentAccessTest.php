<?php

namespace Tests\Feature;

use App\Models\LoanApplication;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoanAssignmentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_loan_officer_lists_only_applications_assigned_to_them(): void
    {
        [$tenant, $applicant, $officer, $otherOfficer] = $this->team();
        $assignedLoan = $this->loan($tenant, $applicant, $officer);
        $this->loan($tenant, $applicant, $otherOfficer);
        $this->loan($tenant, $applicant);
        Sanctum::actingAs($officer);

        $this->getJson('/api/loan-applications')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $assignedLoan->id);
    }

    public function test_loan_officer_cannot_view_an_unassigned_or_other_officers_loan(): void
    {
        [$tenant, $applicant, $officer, $otherOfficer] = $this->team();
        $otherLoan = $this->loan($tenant, $applicant, $otherOfficer);
        $unassignedLoan = $this->loan($tenant, $applicant);
        Sanctum::actingAs($officer);

        $this->getJson("/api/loan-applications/{$otherLoan->id}")->assertForbidden();
        $this->getJson("/api/loan-applications/{$unassignedLoan->id}")->assertForbidden();
    }

    public function test_unassigned_loan_officer_cannot_transition_status(): void
    {
        [$tenant, $applicant, $officer] = $this->team();
        $loan = $this->loan($tenant, $applicant);
        Sanctum::actingAs($officer);

        $this->postJson("/api/loan-applications/{$loan->id}/transition", [
            'to_status' => 'under_review',
        ])->assertForbidden();
    }

    public function test_different_loan_officer_cannot_transition_another_officers_case(): void
    {
        [$tenant, $applicant, $officer, $otherOfficer] = $this->team();
        $loan = $this->loan($tenant, $applicant, $officer);
        Sanctum::actingAs($otherOfficer);

        $this->postJson("/api/loan-applications/{$loan->id}/transition", [
            'to_status' => 'under_review',
        ])->assertForbidden();
    }

    public function test_assigned_loan_officer_can_transition_status(): void
    {
        [$tenant, $applicant, $officer] = $this->team();
        $loan = $this->loan($tenant, $applicant, $officer);
        Sanctum::actingAs($officer);

        $this->postJson("/api/loan-applications/{$loan->id}/transition", [
            'to_status' => 'under_review',
        ])->assertOk()
            ->assertJsonPath('data.status', 'under_review');

        $this->assertDatabaseHas('loan_applications', [
            'id' => $loan->id,
            'status' => 'under_review',
        ]);
    }

    public function test_bank_admin_cannot_transition_status_even_when_officer_is_assigned(): void
    {
        [$tenant, $applicant, $officer, , $admin] = $this->team();
        $loan = $this->loan($tenant, $applicant, $officer);
        Sanctum::actingAs($admin);

        $this->postJson("/api/loan-applications/{$loan->id}/transition", [
            'to_status' => 'under_review',
        ])->assertForbidden();
    }

    public function test_applicant_can_still_submit_their_own_draft(): void
    {
        [$tenant, $applicant] = $this->team();
        $loan = $this->loan($tenant, $applicant, null, 'draft');
        Sanctum::actingAs($applicant);

        $this->postJson("/api/loan-applications/{$loan->id}/transition", [
            'to_status' => 'submitted',
        ])->assertOk()
            ->assertJsonPath('data.status', 'submitted');
    }

    private function team(): array
    {
        $tenant = Tenant::create([
            'name' => 'Assignment Access Bank',
            'slug' => 'assignment-access-' . str()->lower(str()->random(8)),
        ]);

        $applicant = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'applicant',
        ]);
        $officer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'loan_officer',
        ]);
        $otherOfficer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'loan_officer',
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'bank_admin',
        ]);

        return [$tenant, $applicant, $officer, $otherOfficer, $admin];
    }

    private function loan(
        Tenant $tenant,
        User $applicant,
        ?User $assignee = null,
        string $status = 'submitted',
    ): LoanApplication {
        return LoanApplication::create([
            'tenant_id' => $tenant->id,
            'applicant_id' => $applicant->id,
            'assigned_to' => $assignee?->id,
            'amount' => '1500.00',
            'purpose' => 'Assignment access test',
            'status' => $status,
        ]);
    }
}