<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
    $demo  = Tenant::firstOrCreate(
    ['slug' => 'demo-bank'],
    ['name' => 'Demo Bank']
);
$other = Tenant::firstOrCreate(
    ['slug' => 'second-bank'],
    ['name' => 'Second Bank']
);

        $people = [
            // email                  name          role            tenant
            ['admin@admin.com',      'Platform Admin', 'admin',        null],
            ['bea@example.com',      'Bea',            'bank_admin',   $demo->id],
            ['lena@example.com',     'Lena',           'loan_officer', $demo->id],
            ['omar@example.com',     'Omar',           'loan_officer', $demo->id],
            ['jane@example.com',     'Jane',           'applicant',    $demo->id],
            ['mark@example.com',     'Mark',           'applicant',    $demo->id],
            ['carl@example.com',     'Carl',           'bank_admin',   $other->id], // cross-tenant probe
        ];

        foreach ($people as [$email, $name, $role, $tenantId]) {
            $user = User::withoutGlobalScopes()->firstOrNew(['email' => $email]);
            $user->forceFill([
                'name'      => $name,
                'role'      => $role,
                'tenant_id' => $tenantId,
                'password'  => Hash::make('password123'),
            ])->save();
        }
    }
}