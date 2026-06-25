<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRegistrationTest extends TestCase
{
    use RefreshDatabase;

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

        $response->assertCreated();
        $response->assertJsonStructure([
            'token_type',
            'token',
            'data' => [
                'id',
                'full_name',
                'name',
                'email',
                'contact_number',
                'address',
                'is_admin',
            ],
        ]);
    }
}
