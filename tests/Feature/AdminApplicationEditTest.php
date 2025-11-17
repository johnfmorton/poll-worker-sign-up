<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApplicationEditTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin can view edit form.
     */
    public function test_admin_can_view_edit_form(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'street_address' => '123 Main St',
        ]);

        // Act as admin and visit edit page
        $response = $this->actingAs($admin)->get(route('admin.applications.edit', $application->id));

        // Assert page loads successfully
        $response->assertStatus(200);
        $response->assertSee('Edit Application');
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
        $response->assertSee('123 Main St');
    }

    /**
     * Test that non-admin cannot view edit form.
     */
    public function test_non_admin_cannot_view_edit_form(): void
    {
        // Create regular user
        $user = User::factory()->create(['is_admin' => false]);

        // Create application
        $application = Application::factory()->create();

        // Act as regular user and try to visit edit page
        $response = $this->actingAs($user)->get(route('admin.applications.edit', $application->id));

        // Assert access is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that guest cannot view edit form.
     */
    public function test_guest_cannot_view_edit_form(): void
    {
        // Create application
        $application = Application::factory()->create();

        // Try to visit edit page without authentication
        $response = $this->get(route('admin.applications.edit', $application->id));

        // Assert access is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that admin can update application with valid data.
     */
    public function test_admin_can_update_application_with_valid_data(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'street_address' => '123 Main St',
        ]);

        // Act as admin and update application
        $response = $this->actingAs($admin)->put(
            route('admin.applications.update', $application->id),
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'street_address' => '456 Oak Ave',
            ]
        );

        // Assert redirect to detail page with success message
        $response->assertRedirect(route('admin.applications.show', $application->id));
        $response->assertSessionHas('success', 'Application updated successfully.');

        // Assert database was updated
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'street_address' => '456 Oak Ave',
        ]);
    }

    /**
     * Test that update requires name field.
     */
    public function test_update_requires_name_field(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Try to update without name
        $response = $this->actingAs($admin)->put(
            route('admin.applications.update', $application->id),
            [
                'name' => '',
                'email' => 'test@example.com',
                'street_address' => '123 Main St',
            ]
        );

        // Assert validation error
        $response->assertSessionHasErrors('name');
    }

    /**
     * Test that update requires email field.
     */
    public function test_update_requires_email_field(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Try to update without email
        $response = $this->actingAs($admin)->put(
            route('admin.applications.update', $application->id),
            [
                'name' => 'John Doe',
                'email' => '',
                'street_address' => '123 Main St',
            ]
        );

        // Assert validation error
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that update requires valid email format.
     */
    public function test_update_requires_valid_email_format(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Try to update with invalid email
        $response = $this->actingAs($admin)->put(
            route('admin.applications.update', $application->id),
            [
                'name' => 'John Doe',
                'email' => 'invalid-email',
                'street_address' => '123 Main St',
            ]
        );

        // Assert validation error
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that update requires street address field.
     */
    public function test_update_requires_street_address_field(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Try to update without street address
        $response = $this->actingAs($admin)->put(
            route('admin.applications.update', $application->id),
            [
                'name' => 'John Doe',
                'email' => 'test@example.com',
                'street_address' => '',
            ]
        );

        // Assert validation error
        $response->assertSessionHasErrors('street_address');
    }

    /**
     * Test that non-admin cannot update application.
     */
    public function test_non_admin_cannot_update_application(): void
    {
        // Create regular user
        $user = User::factory()->create(['is_admin' => false]);

        // Create application
        $application = Application::factory()->create();

        // Try to update as non-admin
        $response = $this->actingAs($user)->put(
            route('admin.applications.update', $application->id),
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'street_address' => '456 Oak Ave',
            ]
        );

        // Assert access is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that guest cannot update application.
     */
    public function test_guest_cannot_update_application(): void
    {
        // Create application
        $application = Application::factory()->create();

        // Try to update without authentication
        $response = $this->put(
            route('admin.applications.update', $application->id),
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'street_address' => '456 Oak Ave',
            ]
        );

        // Assert access is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that name field respects max length.
     */
    public function test_name_field_respects_max_length(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Try to update with name exceeding 255 characters
        $response = $this->actingAs($admin)->put(
            route('admin.applications.update', $application->id),
            [
                'name' => str_repeat('a', 256),
                'email' => 'test@example.com',
                'street_address' => '123 Main St',
            ]
        );

        // Assert validation error
        $response->assertSessionHasErrors('name');
    }

    /**
     * Test that email field respects max length.
     */
    public function test_email_field_respects_max_length(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Try to update with email exceeding 255 characters
        $response = $this->actingAs($admin)->put(
            route('admin.applications.update', $application->id),
            [
                'name' => 'John Doe',
                'email' => str_repeat('a', 246).'@example.com', // 256 total
                'street_address' => '123 Main St',
            ]
        );

        // Assert validation error
        $response->assertSessionHasErrors('email');
    }

    /**
     * Test that street address field respects max length.
     */
    public function test_street_address_field_respects_max_length(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Try to update with street address exceeding 500 characters
        $response = $this->actingAs($admin)->put(
            route('admin.applications.update', $application->id),
            [
                'name' => 'John Doe',
                'email' => 'test@example.com',
                'street_address' => str_repeat('a', 501),
            ]
        );

        // Assert validation error
        $response->assertSessionHasErrors('street_address');
    }
}
