<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_update_name_and_email(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/me', [
            'name' => 'Updated User',
            'email' => 'updated@example.com',
        ])
            ->assertOk()
            ->assertJsonPath('name', 'Updated User')
            ->assertJsonPath('email', 'updated@example.com');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_authenticated_user_can_change_password_with_current_password(): void
    {
        $user = User::factory()->create(['password' => 'old-password']);
        Sanctum::actingAs($user);

        $this->putJson('/api/me/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_password_change_rejects_an_incorrect_current_password(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putJson('/api/me/password', [
            'current_password' => 'incorrect-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('current_password');
    }
}