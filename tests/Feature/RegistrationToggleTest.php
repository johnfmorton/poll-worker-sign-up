<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationToggleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin can view registration toggle on dashboard.
     */
    public function test_dashboard_displays_registration_toggle(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Public Registration');
        $response->assertSee('Control whether the public can submit new poll worker applications');
    }

    /**
     * Test that admin can disable registration.
     */
    public function test_admin_can_disable_registration(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Setting::set('registration_enabled', true);

        $response = $this->actingAs($admin)->post(route('admin.toggleRegistration'), [
            'enabled' => false,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Registration has been disabled.');
        $this->assertFalse(Setting::isRegistrationEnabled());
    }

    /**
     * Test that admin can enable registration.
     */
    public function test_admin_can_enable_registration(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Setting::set('registration_enabled', false);

        $response = $this->actingAs($admin)->post(route('admin.toggleRegistration'), [
            'enabled' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Registration has been enabled.');
        $this->assertTrue(Setting::isRegistrationEnabled());
    }

    /**
     * Test that toggle requires enabled parameter.
     */
    public function test_toggle_requires_enabled_parameter(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.toggleRegistration'), []);

        $response->assertSessionHasErrors('enabled');
    }

    /**
     * Test that toggle requires boolean value.
     */
    public function test_toggle_requires_boolean_value(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.toggleRegistration'), [
            'enabled' => 'invalid',
        ]);

        $response->assertSessionHasErrors('enabled');
    }

    /**
     * Test that non-admin cannot toggle registration.
     */
    public function test_non_admin_cannot_toggle_registration(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->post(route('admin.toggleRegistration'), [
            'enabled' => false,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test that guest cannot toggle registration.
     */
    public function test_guest_cannot_toggle_registration(): void
    {
        $response = $this->post(route('admin.toggleRegistration'), [
            'enabled' => false,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test that dashboard shows correct status badge when enabled.
     */
    public function test_dashboard_shows_enabled_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Setting::set('registration_enabled', true);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Enabled');
        $response->assertSee('Disable Registration');
    }

    /**
     * Test that dashboard shows correct status badge when disabled.
     */
    public function test_dashboard_shows_disabled_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Setting::set('registration_enabled', false);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Disabled');
        $response->assertSee('Enable Registration');
    }
}
