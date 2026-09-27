<?php

namespace Database\Seeders;

use App\Models\LoanApplication;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo seed data must not be installed in production.');
        }

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo-bank'],
            ['name' => 'Demo Bank', 'is_active' => true],
        );

        $applicant = User::firstOrCreate(
            ['email' => 'demo.applicant@example.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Demo Applicant',
                'password' => 'DemoLoan2026!',
                'role' => 'applicant',
            ],
        );

        $applications = [
            ['amount' => 12500, 'purpose' => 'Home repairs', 'status' => 'draft'],
            ['amount' => 8000, 'purpose' => 'Professional training', 'status' => 'submitted'],
            ['amount' => 25000, 'purpose' => 'Used vehicle purchase', 'status' => 'under_review'],
        ];

        foreach ($applications as $application) {
            LoanApplication::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'applicant_id' => $applicant->id,
                    'purpose' => $application['purpose'],
                ],
                [
                    'amount' => $application['amount'],
                    'status' => $application['status'],
                ],
            );
        }
    }
}
