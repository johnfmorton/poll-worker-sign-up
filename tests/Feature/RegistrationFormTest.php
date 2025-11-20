<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the registration form page loads successfully.
     */
    public function test_registration_form_displays(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Poll Worker Registration');
        $response->assertSee('Full Name');
        $response->assertSee('Email Address');
        $response->assertSee('Street Address');
        $response->assertSee('Submit Your Information');
    }

    /**
     * Test that CSRF protection is present.
     */
    public function test_registration_form_has_csrf_protection(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('_token', false);
    }
}
