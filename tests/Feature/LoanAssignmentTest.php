<?php

namespace Tests\Feature;

use App\Models\LoanApplication;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoanAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_bank_admin_can_assign_a_loan_to_a_same_tenant_officer(): void
    {
        [$tenant, $admin, $officer, $loan] = $this->loanTeam();
        Sanctum::actingAs($admin);

        $this->patchJson("/api/loan-applications/{$loan->id}/assign", [
            'assigned_to' => $officer->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.assignee.id', $officer->id)
            ->assertJsonPath('data.assignee.name', $officer->name);

        $this->assertDatabaseHas('loan_applications', [
            'id' => $loan->id,
            'assigned_to' => $officer->id,
        ]);
    }

    public function test_bank_admin_can_clear_a_loan_assignment(): void
    {
        [$tenant, $admin, $officer, $loan] = $this->loanTeam();
        $loan->update(['assigned_to' => $officer->id]);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/loan-applications/{$loan->id}/assign", [
            'assigned_to' => null,
        ])->assertOk()
            ->assertJsonPath('data.assignee', null);

        $this->assertDatabaseHas('loan_applications', [
            'id' => $loan->id,
            'assigned_to' => null,
        ]);
    }

    public function test_bank_admin_cannot_assign_a_loan_to_another_tenant_officer(): void
    {
        [, $admin, , $loan] = $this->loanTeam();
        $otherTenant = Tenant::create([
            'id' => (string) str()->uuid(),
            'name' => 'Other Bank',
            'slug' => 'other-bank',
        ]);
        $otherOfficer = User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'role' => 'loan_officer',
        ]);
        Sanctum::actingAs($admin);

        $this->patchJson("/api/loan-applications/{$loan->id}/assign", [
            'assigned_to' => $otherOfficer->id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('assigned_to');
    }

    public function test_loan_officer_cannot_assign_a_loan(): void
    {
        [, , $officer, $loan] = $this->loanTeam();
        Sanctum::actingAs($officer);

        $this->patchJson("/api/loan-applications/{$loan->id}/assign", [
            'assigned_to' => $officer->id,
        ])->assertForbidden();
    }

    private function loanTeam(): array
    {
        $tenant = Tenant::create([
            'id' => (string) str()->uuid(),
            'name' => 'Test Bank',
            'slug' => 'test-bank',
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'bank_admin',
        ]);
        $officer = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'loan_officer',
        ]);
        $applicant = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'applicant',
        ]);
        $loan = LoanApplication::create([
            'tenant_id' => $tenant->id,
            'applicant_id' => $applicant->id,
            'amount' => '1000.00',
            'purpose' => 'Assignment test',
            'status' => 'under_review',
        ]);

        return [$tenant, $admin, $officer, $loan];
    }
}