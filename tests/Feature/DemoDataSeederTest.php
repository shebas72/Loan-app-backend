<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_a_demo_tenant_user_and_loans_idempotently(): void
    {
        $this->seed(DatabaseSeeder::class);

        $applicant = User::where('email', 'demo.applicant@example.com')->firstOrFail();

        $this->assertSame('Demo Bank', $applicant->tenant->name);
        $this->assertSame('applicant', $applicant->role);
        $this->assertTrue(Hash::check('DemoLoan2026!', $applicant->password));
        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('loan_applications', 3);

        $login = $this->postJson('/api/login', [
            'email' => 'demo.applicant@example.com',
            'password' => 'DemoLoan2026!',
        ])->assertOk();

        $this->withToken($login->json('token'))
            ->getJson('/api/loan-applications')
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('tenants', 1);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('loan_applications', 3);
    }
}