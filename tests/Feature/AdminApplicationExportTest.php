<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApplicationExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin can export applications to CSV.
     */
    public function test_admin_can_export_applications_to_csv(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create some test applications
        $application1 = Application::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'street_address' => '123 Main St',
            'residency_status' => 'approved',
            'party_affiliation' => 'democrat',
        ]);

        $application2 = Application::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'street_address' => '456 Oak Ave',
            'residency_status' => 'pending',
        ]);

        // Make request as admin
        $response = $this->actingAs($admin)->get(route('admin.applications.export'));

        // Assert response is successful
        $response->assertStatus(200);

        // Assert correct headers
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');

        // Get the response content
        $content = $response->streamedContent();

        // Assert CSV contains header row
        $this->assertStringContainsString('ID', $content);
        $this->assertStringContainsString('Name', $content);
        $this->assertStringContainsString('Email', $content);
        $this->assertStringContainsString('Street Address', $content);
        $this->assertStringContainsString('Email Verified', $content);
        $this->assertStringContainsString('Residency Status', $content);
        $this->assertStringContainsString('Party Affiliation', $content);

        // Assert CSV contains application data
        $this->assertStringContainsString('John Doe', $content);
        $this->assertStringContainsString('john@example.com', $content);
        $this->assertStringContainsString('123 Main St', $content);
        $this->assertStringContainsString('Jane Smith', $content);
        $this->assertStringContainsString('jane@example.com', $content);
        $this->assertStringContainsString('456 Oak Ave', $content);
    }

    /**
     * Test that non-admin cannot export applications.
     */
    public function test_non_admin_cannot_export_applications(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.applications.export'));

        $response->assertStatus(403);
    }

    /**
     * Test that guest cannot export applications.
     */
    public function test_guest_cannot_export_applications(): void
    {
        $response = $this->get(route('admin.applications.export'));

        $response->assertStatus(403);
    }

    /**
     * Test that CSV includes timestamps and admin names.
     */
    public function test_csv_includes_timestamps_and_admin_names(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $validator = User::factory()->create([
            'name' => 'Validator Admin',
            'is_admin' => true,
        ]);

        $application = Application::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'residency_status' => 'approved',
            'residency_validated_by' => $validator->id,
            'residency_validated_at' => now(),
            'party_affiliation' => 'republican',
            'party_assigned_by' => $validator->id,
            'party_assigned_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.applications.export'));

        $content = $response->streamedContent();

        // Assert admin name is included
        $this->assertStringContainsString('Validator Admin', $content);

        // Assert timestamp columns exist
        $this->assertStringContainsString('Email Verified At', $content);
        $this->assertStringContainsString('Residency Validated At', $content);
        $this->assertStringContainsString('Party Assigned At', $content);
        $this->assertStringContainsString('Created At', $content);
        $this->assertStringContainsString('Updated At', $content);
    }

    /**
     * Test that CSV filename includes current date.
     */
    public function test_csv_filename_includes_date(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.applications.export'));

        $contentDisposition = $response->headers->get('Content-Disposition');
        $expectedDate = date('Y-m-d');

        $this->assertStringContainsString("poll-workers-{$expectedDate}.csv", $contentDisposition);
    }
}
