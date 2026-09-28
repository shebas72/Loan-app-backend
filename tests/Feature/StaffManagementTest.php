<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_bank_admin_sees_no_staff_until_loan_officers_are_added(): void
    {
        $tenant = Tenant::create([
            'id' => (string) str()->uuid(),
            'name' => 'New Bank',
            'slug' => 'new-bank',
        ]);
        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'bank_admin',
        ]);
        Sanctum::actingAs($admin);

        $this->getJson('/api/staff')
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }

    public function test_bank_admin_can_update_loan_officer_details(): void
    {
        [$admin, $officer] = $this->staffPair();
        Sanctum::actingAs($admin);

        $this->patchJson("/api/staff/{$officer->id}", [
            'name' => 'Updated Officer',
            'email' => 'updated.officer@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Officer')
            ->assertJsonPath('data.email', 'updated.officer@example.com');
    }

    public function test_disabling_a_loan_officer_revokes_their_tokens(): void
    {
        [$admin, $officer] = $this->staffPair();
        $officer->createToken('test-token');
        Sanctum::actingAs($admin);

        $this->patchJson("/api/staff/{$officer->id}/status", ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('users', ['id' => $officer->id, 'is_active' => false]);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $officer->id]);
    }

    public function test_bank_admin_can_delete_a_loan_officer(): void
    {
        [$admin, $officer] = $this->staffPair();
        Sanctum::actingAs($admin);

        $this->deleteJson("/api/staff/{$officer->id}")->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $officer->id]);
    }

    public function test_disabled_staff_cannot_log_in(): void
    {
        [, $officer] = $this->staffPair();
        $officer->update(['is_active' => false]);

        $this->postJson('/api/login', [
            'email' => $officer->email,
            'password' => 'password',
        ])->assertForbidden()
            ->assertJsonPath('message', 'Your account has been disabled. Please contact your bank administrator for assistance.');

        $this->postJson('/api/login', [
            'email' => $officer->email,
            'password' => 'incorrect-password',
        ])->assertUnprocessable()
            ->assertJsonPath('message', 'The provided credentials are incorrect.');
    }

    public function test_bank_admin_cannot_manage_staff_from_another_tenant(): void
    {
        [$admin] = $this->staffPair();
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

        $this->deleteJson("/api/staff/{$otherOfficer->id}")->assertNotFound();
    }

    private function staffPair(): array
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

        return [$admin, $officer];
    }
}
