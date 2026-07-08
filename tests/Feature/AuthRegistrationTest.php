<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_email_check_rejects_existing_email(): void
    {
        User::factory()->create([
            'email' => 'claimed@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/register/check-email', [
            'email' => 'claimed@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'This email is already claimed or used.');
    }

    public function test_registration_email_check_accepts_available_email(): void
    {
        $response = $this->postJson('/api/register/check-email', [
            'email' => 'available@example.com',
        ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'This email is available.');
    }

    public function test_registration_requires_strong_password_and_matching_confirmation(): void
    {
        $response = $this->postJson('/api/register', [
            'full_name' => 'Juan Dela Cruz Jr',
            'email' => 'juan@example.com',
            'password' => 'weakpass',
            'password_confirmation' => 'weakpass',
            'contact_number' => '639171234567',
            'address' => 'Sample Address',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_registration_requires_contact_number_with_63_prefix(): void
    {
        $response = $this->postJson('/api/register', [
            'full_name' => 'Juan Dela Cruz Jr',
            'email' => 'juan2@example.com',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
            'contact_number' => '09171234567',
            'address' => 'Sample Address',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['contact_number']);
    }

    public function test_registration_succeeds_with_valid_payload(): void
    {
        $response = $this->postJson('/api/register', [
            'full_name' => 'Juan Miguel Dela Cruz Jr',
            'email' => 'juan3@example.com',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
            'contact_number' => '639171234567',
            'address' => 'Sample Address',
        ]);

        $response->assertStatus(202);
        $response->assertJsonStructure([
            'message',
        ]);
    }

    public function test_registration_rejects_duplicate_email_with_clear_message(): void
    {
        User::factory()->create([
            'email' => 'duplicate@example.com',
            'email_verified_at' => now(),
        ]);

        $duplicateResponse = $this->postJson('/api/register', [
            'full_name' => 'Existing User 2',
            'email' => 'duplicate@example.com',
            'password' => 'StrongPass1!',
            'password_confirmation' => 'StrongPass1!',
            'contact_number' => '639171234568',
            'address' => 'Another Address',
        ]);

        $duplicateResponse->assertStatus(422);
        $duplicateResponse->assertJsonValidationErrors(['email']);
        $duplicateResponse->assertJsonPath('errors.email.0', 'This email is already claimed or used.');
    }
}
