<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Dashboard');
    }

    public function test_non_admin_cannot_access_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_dashboard_displays_total_applications_count(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Application::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Total Applications');
        $response->assertSee('5');
    }

    public function test_dashboard_displays_pending_residency_count(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Application::factory()->count(3)->create(['residency_status' => 'pending']);
        Application::factory()->count(2)->create(['residency_status' => 'approved']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Pending Residency');
        $response->assertSee('3');
    }

    public function test_dashboard_displays_verified_awaiting_approval_count(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        // Verified and pending
        Application::factory()->count(2)->create([
            'email_verified_at' => now(),
            'residency_status' => 'pending',
        ]);
        
        // Unverified and pending
        Application::factory()->count(1)->create([
            'email_verified_at' => null,
            'residency_status' => 'pending',
        ]);
        
        // Verified and approved
        Application::factory()->count(1)->create([
            'email_verified_at' => now(),
            'residency_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Verified Awaiting Approval');
        $response->assertSee('2');
    }

    public function test_dashboard_displays_approved_without_party_count(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        
        // Approved without party
        Application::factory()->count(3)->create([
            'residency_status' => 'approved',
            'party_affiliation' => null,
        ]);
        
        // Approved with party
        Application::factory()->count(2)->create([
            'residency_status' => 'approved',
            'party_affiliation' => 'democrat',
        ]);
        
        // Pending without party
        Application::factory()->count(1)->create([
            'residency_status' => 'pending',
            'party_affiliation' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Approved Without Party');
        $response->assertSee('3');
    }

    public function test_dashboard_has_navigation_links_to_filtered_lists(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('View all applications');
        $response->assertSee('Review pending applications');
        $response->assertSee('Review verified applications');
        $response->assertSee('Assign party affiliations');
    }

    public function test_dashboard_has_quick_action_links(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Quick Actions');
        $response->assertSee('View All Applications');
        $response->assertSee('Unverified Emails');
        $response->assertSee('Approved Residents');
    }
}
