<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RegistrationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Enable registration for tests
        Setting::updateOrCreate(
            ['key' => 'registration_enabled'],
            ['value' => '1']
        );
    }

    /**
     * Test successful registration submission.
     */
    public function test_user_can_submit_registration(): void
    {
        $response = $this->post('/', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'street_address' => '123 Main St',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success', 'Registration submitted! Please check your email to verify your address.');

        $this->assertDatabaseHas('applications', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'street_address' => '123 Main St',
        ]);
    }

    /**
     * Test registration with duplicate unverified email resends verification.
     */
    public function test_duplicate_unverified_email_resends_verification(): void
    {
        // Create an existing unverified application
        $token = Str::random(64);
        Application::create([
            'name' => 'Yukihiro Matsumoto',
            'email' => 'matz@example.com',
            'street_address' => '456 Ruby Lane',
            'verification_token' => $token,
            'verification_token_expires_at' => Carbon::now()->addHours(48),
            'residency_status' => 'pending',
        ]);

        // Try to register again with the same email
        $response = $this->post('/', [
            'name' => 'Yukihiro Matsumoto',
            'email' => 'matz@example.com',
            'street_address' => '456 Ruby Lane',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('info');
        $this->assertStringContainsString('already registered but not yet verified', session('info'));
        $this->assertStringContainsString('resent the verification email', session('info'));
        $this->assertStringContainsString('registrars@warrenct.gov', session('info'));

        // Verify the application still exists (not duplicated)
        $this->assertEquals(1, Application::where('email', 'matz@example.com')->count());

        // Verify the token was regenerated
        $application = Application::where('email', 'matz@example.com')->first();
        $this->assertNotEquals($token, $application->verification_token);
    }

    /**
     * Test registration requires name field.
     */
    public function test_registration_requires_name(): void
    {
        $response = $this->post('/', [
            'email' => 'test@example.com',
            'street_address' => '123 Main St',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /**
     * Test registration requires email field.
     */
    public function test_registration_requires_email(): void
    {
        $response = $this->post('/', [
            'name' => 'John Doe',
            'street_address' => '123 Main St',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test registration requires valid email format.
     */
    public function test_registration_requires_valid_email(): void
    {
        $response = $this->post('/', [
            'name' => 'John Doe',
            'email' => 'not-an-email',
            'street_address' => '123 Main St',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * Test registration requires street address field.
     */
    public function test_registration_requires_street_address(): void
    {
        $response = $this->post('/', [
            'name' => 'John Doe',
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('street_address');
    }

    /**
     * Test registration is blocked when disabled.
     */
    public function test_registration_blocked_when_disabled(): void
    {
        // Disable registration
        Setting::updateOrCreate(
            ['key' => 'registration_enabled'],
            ['value' => '0']
        );

        $response = $this->post('/', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'street_address' => '123 Main St',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('error', 'Registration is currently disabled. Please contact the registrar\'s office.');

        $this->assertDatabaseMissing('applications', [
            'email' => 'john@example.com',
        ]);
    }
}
