<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class RegistrationFormTest extends TestCase
{
    /**
     * Test that the registration form page loads successfully.
     */
    public function test_registration_form_displays(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Poll Worker Registration');
        $response->assertSee('Full Name');
        $response->assertSee('Email Address');
        $response->assertSee('Street Address');
        $response->assertSee('Submit Registration');
    }

    /**
     * Test that CSRF protection is present.
     */
    public function test_registration_form_has_csrf_protection(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('_token', false);
    }
}
