<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApplicationListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin can access the applications list page.
     */
    public function test_admin_can_access_applications_list(): void
    {
        // Create an admin user
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        // Create some applications
        Application::factory()->count(3)->create();

        // Act as admin and visit the applications list
        $response = $this->actingAs($admin)->get(route('admin.applications.index'));

        // Assert the page loads successfully
        $response->assertStatus(200);
        $response->assertViewIs('admin.applications.index');
        $response->assertViewHas('applications');
    }

    /**
     * Test that non-admin users cannot access the applications list.
     */
    public function test_non_admin_cannot_access_applications_list(): void
    {
        // Create a regular user
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        // Try to access the applications list
        $response = $this->actingAs($user)->get(route('admin.applications.index'));

        // Assert access is denied
        $response->assertStatus(403);
    }

    /**
     * Test that unauthenticated users cannot access the applications list.
     */
    public function test_unauthenticated_users_cannot_access_applications_list(): void
    {
        $response = $this->get(route('admin.applications.index'));

        // Assert redirect to login
        $response->assertStatus(403);
    }

    /**
     * Test filtering applications by search query.
     */
    public function test_admin_can_filter_applications_by_search(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Create applications with different names
        Application::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        Application::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        // Search for "John"
        $response = $this->actingAs($admin)->get(route('admin.applications.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertDontSee('Jane Smith');
    }

    /**
     * Test filtering applications by residency status.
     */
    public function test_admin_can_filter_applications_by_residency_status(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Create applications with different residency statuses
        Application::factory()->create(['name' => 'Approved User', 'residency_status' => 'approved']);
        Application::factory()->create(['name' => 'Pending User', 'residency_status' => 'pending']);

        // Filter by approved status
        $response = $this->actingAs($admin)->get(route('admin.applications.index', ['residency_status' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('Approved User');
        $response->assertDontSee('Pending User');
    }

    /**
     * Test filtering applications by party affiliation.
     */
    public function test_admin_can_filter_applications_by_party_affiliation(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Create applications with different party affiliations
        Application::factory()->create(['name' => 'Democrat User', 'party_affiliation' => 'democrat']);
        Application::factory()->create(['name' => 'Republican User', 'party_affiliation' => 'republican']);

        // Filter by democrat
        $response = $this->actingAs($admin)->get(route('admin.applications.index', ['party_affiliation' => 'democrat']));

        $response->assertStatus(200);
        $response->assertSee('Democrat User');
        $response->assertDontSee('Republican User');
    }

    /**
     * Test filtering applications by email verification status.
     */
    public function test_admin_can_filter_applications_by_email_verification(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Create verified and unverified applications
        Application::factory()->create([
            'name' => 'Verified User',
            'email_verified_at' => now(),
        ]);
        Application::factory()->create([
            'name' => 'Unverified User',
            'email_verified_at' => null,
        ]);

        // Filter by verified
        $response = $this->actingAs($admin)->get(route('admin.applications.index', ['email_verified' => 'yes']));

        $response->assertStatus(200);
        $response->assertSee('Verified User');
        $response->assertDontSee('Unverified User');
    }

    /**
     * Test that applications are paginated.
     */
    public function test_applications_are_paginated(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        // Create more than 20 applications (default pagination)
        Application::factory()->count(25)->create();

        $response = $this->actingAs($admin)->get(route('admin.applications.index'));

        $response->assertStatus(200);
        // Check that pagination links are present
        $response->assertViewHas('applications', function ($applications) {
            return $applications->total() === 25 && $applications->perPage() === 20;
        });
    }
}
