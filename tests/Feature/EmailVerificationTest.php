<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Application;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test successful email verification with valid token.
     */
    public function test_email_verification_succeeds_with_valid_token(): void
    {
        // Create an application with a valid verification token
        $token = Str::random(64);
        $application = Application::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'street_address' => '123 Main St',
            'verification_token' => $token,
            'verification_token_expires_at' => Carbon::now()->addHours(48),
            'residency_status' => 'pending',
        ]);

        // Visit the verification URL
        $response = $this->get("/verify/{$token}");

        $response->assertStatus(200);
        $response->assertSee('Email Verified Successfully!');
        $response->assertSee('Thank you for verifying your email address');

        // Verify the application was updated
        $application->refresh();
        $this->assertNotNull($application->email_verified_at);
        $this->assertNull($application->verification_token);
        $this->assertNull($application->verification_token_expires_at);
        $this->assertNotNull($application->user_id);

        // Verify a user account was created
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
            'is_admin' => false,
        ]);
    }

    /**
     * Test email verification fails with expired token.
     */
    public function test_email_verification_fails_with_expired_token(): void
    {
        // Create an application with an expired verification token
        $token = Str::random(64);
        $application = Application::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'street_address' => '456 Oak Ave',
            'verification_token' => $token,
            'verification_token_expires_at' => Carbon::now()->subHours(1), // Expired 1 hour ago
            'residency_status' => 'pending',
        ]);

        // Visit the verification URL
        $response = $this->get("/verify/{$token}");

        $response->assertStatus(200);
        $response->assertSee('Verification Link Expired');
        $response->assertSee('This verification link has expired or is invalid');
        $response->assertSee('Send New Verification Email');

        // Verify the application was NOT updated
        $application->refresh();
        $this->assertNull($application->email_verified_at);
        $this->assertNotNull($application->verification_token);
        $this->assertNull($application->user_id);

        // Verify no user account was created
        $this->assertDatabaseMissing('users', [
            'email' => 'jane@example.com',
        ]);
    }

    /**
     * Test email verification fails with invalid token.
     */
    public function test_email_verification_fails_with_invalid_token(): void
    {
        $invalid_token = 'invalid-token-that-does-not-exist';

        // Visit the verification URL with invalid token
        $response = $this->get("/verify/{$invalid_token}");

        $response->assertStatus(200);
        $response->assertSee('Verification Link Expired');
        $response->assertSee('This verification link has expired or is invalid');
    }

    /**
     * Test success view displays correct content.
     */
    public function test_success_view_displays_correct_content(): void
    {
        $token = Str::random(64);
        Application::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'street_address' => '789 Pine Rd',
            'verification_token' => $token,
            'verification_token_expires_at' => Carbon::now()->addHours(48),
            'residency_status' => 'pending',
        ]);

        $response = $this->get("/verify/{$token}");

        $response->assertStatus(200);
        $response->assertSee('Email Verified Successfully!');
        $response->assertSee('Your application will be reviewed by the voter registrar');
        $response->assertSee('Return to Home');
    }

    /**
     * Test expired view displays correct content.
     */
    public function test_expired_view_displays_correct_content(): void
    {
        $token = Str::random(64);
        Application::create([
            'name' => 'Expired User',
            'email' => 'expired@example.com',
            'street_address' => '321 Elm St',
            'verification_token' => $token,
            'verification_token_expires_at' => Carbon::now()->subHours(1),
            'residency_status' => 'pending',
        ]);

        $response = $this->get("/verify/{$token}");

        $response->assertStatus(200);
        $response->assertSee('Verification Link Expired');
        $response->assertSee('Verification links are valid for 48 hours');
        $response->assertSee('Return to Home');
    }

    /**
     * Test user can resend verification email from expired page.
     */
    public function test_user_can_resend_verification_email_from_expired_page(): void
    {
        // Create an application with an expired verification token
        $token = Str::random(64);
        $application = Application::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'street_address' => '456 Oak Ave',
            'verification_token' => $token,
            'verification_token_expires_at' => Carbon::now()->subHours(1),
            'residency_status' => 'pending',
        ]);

        // Resend verification email
        $response = $this->post("/verification/resend/jane@example.com");

        $response->assertRedirect(route('verification.expired'));
        $response->assertSessionHas('success', 'A new verification email has been sent to your email address.');

        // Verify the application has a new token
        $application->refresh();
        $this->assertNotEquals($token, $application->verification_token);
        $this->assertGreaterThan(Carbon::now(), $application->verification_token_expires_at);
    }

    /**
     * Test already verified link shows appropriate message.
     */
    public function test_already_verified_link_shows_appropriate_message(): void
    {
        // Create and verify an application first
        $token = Str::random(64);
        $application = Application::create([
            'name' => 'John Verified',
            'email' => 'verified@example.com',
            'street_address' => '789 Verified St',
            'verification_token' => $token,
            'verification_token_expires_at' => Carbon::now()->addHours(48),
            'residency_status' => 'pending',
        ]);

        // Verify the application
        $this->get("/verify/{$token}");

        // Try to visit with the same token again (simulate already used link)
        $response = $this->get("/verify/{$token}");

        $response->assertStatus(200);
        $response->assertSee('Already Verified');
        $response->assertSee('Your account has already been verified');
        $response->assertSee('Thank you for signing up');
    }
}
