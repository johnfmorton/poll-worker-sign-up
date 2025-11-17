<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApplicationDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin can view application detail page.
     */
    public function test_admin_can_view_application_detail(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'street_address' => '123 Main St',
        ]);

        // Act as admin and visit detail page
        $response = $this->actingAs($admin)->get(route('admin.applications.show', $application->id));

        // Assert page loads successfully
        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
        $response->assertSee('123 Main St');
    }

    /**
     * Test that non-admin cannot view application detail page.
     */
    public function test_non_admin_cannot_view_application_detail(): void
    {
        // Create regular user
        $user = User::factory()->create(['is_admin' => false]);

        // Create application
        $application = Application::factory()->create();

        // Act as regular user and try to visit detail page
        $response = $this->actingAs($user)->get(route('admin.applications.show', $application->id));

        // Assert access is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that guest cannot view application detail page.
     */
    public function test_guest_cannot_view_application_detail(): void
    {
        // Create application
        $application = Application::factory()->create();

        // Try to visit detail page without authentication
        $response = $this->get(route('admin.applications.show', $application->id));

        // Assert access is forbidden (admin middleware returns 403 for unauthenticated users)
        $response->assertStatus(403);
    }

    /**
     * Test that application detail page shows residency validation controls.
     */
    public function test_detail_page_shows_residency_validation_controls(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Act as admin and visit detail page
        $response = $this->actingAs($admin)->get(route('admin.applications.show', $application->id));

        // Assert residency controls are present
        $response->assertSee('Approve Residency');
        $response->assertSee('Reject Residency');
    }

    /**
     * Test that application detail page shows party affiliation dropdown.
     */
    public function test_detail_page_shows_party_affiliation_dropdown(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Act as admin and visit detail page
        $response = $this->actingAs($admin)->get(route('admin.applications.show', $application->id));

        // Assert party dropdown is present
        $response->assertSee('Select Party');
        $response->assertSee('Democrat');
        $response->assertSee('Republican');
        $response->assertSee('Independent');
        $response->assertSee('Unaffiliated');
        $response->assertSee('Assign Party');
    }

    /**
     * Test that application detail page shows edit and delete buttons.
     */
    public function test_detail_page_shows_edit_and_delete_buttons(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Act as admin and visit detail page
        $response = $this->actingAs($admin)->get(route('admin.applications.show', $application->id));

        // Assert action buttons are present
        $response->assertSee('Edit Application');
        $response->assertSee('Delete Application');
    }

    /**
     * Test that resend verification button shows only for unverified applications.
     */
    public function test_resend_verification_button_shows_for_unverified_applications(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create unverified application
        $unverified_application = Application::factory()->create([
            'email_verified_at' => null,
        ]);

        // Act as admin and visit detail page
        $response = $this->actingAs($admin)->get(route('admin.applications.show', $unverified_application->id));

        // Assert resend button is present
        $response->assertSee('Resend Verification Email');
    }

    /**
     * Test that resend verification button does not show for verified applications.
     */
    public function test_resend_verification_button_does_not_show_for_verified_applications(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create verified application
        $verified_application = Application::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Act as admin and visit detail page
        $response = $this->actingAs($admin)->get(route('admin.applications.show', $verified_application->id));

        // Assert resend button form action is not present
        $response->assertDontSee(route('admin.applications.resendVerification', $verified_application->id));
    }

    /**
     * Test that validation history is displayed when present.
     */
    public function test_validation_history_is_displayed(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);
        $validator = User::factory()->create(['name' => 'Validator Admin']);

        // Create application with validation history
        $application = Application::factory()->create([
            'residency_status' => 'approved',
            'residency_validated_at' => now(),
            'residency_validated_by' => $validator->id,
        ]);

        // Act as admin and visit detail page
        $response = $this->actingAs($admin)->get(route('admin.applications.show', $application->id));

        // Assert validation history is shown
        $response->assertSee('Validated by:');
        $response->assertSee('Validator Admin');
    }

    /**
     * Test that party assignment history is displayed when present.
     */
    public function test_party_assignment_history_is_displayed(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);
        $assigner = User::factory()->create(['name' => 'Assigner Admin']);

        // Create application with party assignment history
        $application = Application::factory()->create([
            'party_affiliation' => 'democrat',
            'party_assigned_at' => now(),
            'party_assigned_by' => $assigner->id,
        ]);

        // Act as admin and visit detail page
        $response = $this->actingAs($admin)->get(route('admin.applications.show', $application->id));

        // Assert assignment history is shown
        $response->assertSee('Assigned by:');
        $response->assertSee('Assigner Admin');
    }

    /**
     * Test that admin can approve residency status.
     */
    public function test_admin_can_approve_residency_status(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application with pending residency
        $application = Application::factory()->create([
            'residency_status' => 'pending',
        ]);

        // Act as admin and approve residency
        $response = $this->actingAs($admin)->post(
            route('admin.applications.updateResidency', $application->id),
            ['residency_status' => 'approved']
        );

        // Assert redirect back with success message
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Residency status updated successfully.');

        // Assert database was updated
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'residency_status' => 'approved',
            'residency_validated_by' => $admin->id,
        ]);

        // Assert timestamp was set
        $application->refresh();
        $this->assertNotNull($application->residency_validated_at);
    }

    /**
     * Test that admin can reject residency status.
     */
    public function test_admin_can_reject_residency_status(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application with pending residency
        $application = Application::factory()->create([
            'residency_status' => 'pending',
        ]);

        // Act as admin and reject residency
        $response = $this->actingAs($admin)->post(
            route('admin.applications.updateResidency', $application->id),
            ['residency_status' => 'rejected']
        );

        // Assert redirect back with success message
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Residency status updated successfully.');

        // Assert database was updated
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'residency_status' => 'rejected',
            'residency_validated_by' => $admin->id,
        ]);

        // Assert timestamp was set
        $application->refresh();
        $this->assertNotNull($application->residency_validated_at);
    }

    /**
     * Test that admin can change residency status after initial validation.
     */
    public function test_admin_can_change_residency_status_after_initial_validation(): void
    {
        // Create admin users
        $admin1 = User::factory()->create(['is_admin' => true, 'name' => 'Admin One']);
        $admin2 = User::factory()->create(['is_admin' => true, 'name' => 'Admin Two']);

        // Create application with approved residency
        $application = Application::factory()->create([
            'residency_status' => 'approved',
            'residency_validated_at' => now()->subDay(),
            'residency_validated_by' => $admin1->id,
        ]);

        // Act as different admin and change to rejected
        $response = $this->actingAs($admin2)->post(
            route('admin.applications.updateResidency', $application->id),
            ['residency_status' => 'rejected']
        );

        // Assert redirect back with success message
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Residency status updated successfully.');

        // Assert database was updated with new admin
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'residency_status' => 'rejected',
            'residency_validated_by' => $admin2->id,
        ]);

        // Assert timestamp was updated
        $application->refresh();
        $this->assertTrue($application->residency_validated_at->isToday());
    }

    /**
     * Test that residency update requires valid status value.
     */
    public function test_residency_update_requires_valid_status(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Try to update with invalid status
        $response = $this->actingAs($admin)->post(
            route('admin.applications.updateResidency', $application->id),
            ['residency_status' => 'invalid']
        );

        // Assert validation error
        $response->assertSessionHasErrors('residency_status');
    }

    /**
     * Test that non-admin cannot update residency status.
     */
    public function test_non_admin_cannot_update_residency_status(): void
    {
        // Create regular user
        $user = User::factory()->create(['is_admin' => false]);

        // Create application
        $application = Application::factory()->create();

        // Try to update residency as non-admin
        $response = $this->actingAs($user)->post(
            route('admin.applications.updateResidency', $application->id),
            ['residency_status' => 'approved']
        );

        // Assert access is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that guest cannot update residency status.
     */
    public function test_guest_cannot_update_residency_status(): void
    {
        // Create application
        $application = Application::factory()->create();

        // Try to update residency without authentication
        $response = $this->post(
            route('admin.applications.updateResidency', $application->id),
            ['residency_status' => 'approved']
        );

        // Assert access is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that admin can assign party affiliation.
     */
    public function test_admin_can_assign_party_affiliation(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application without party affiliation
        $application = Application::factory()->create([
            'party_affiliation' => null,
        ]);

        // Act as admin and assign party
        $response = $this->actingAs($admin)->post(
            route('admin.applications.updateParty', $application->id),
            ['party_affiliation' => 'democrat']
        );

        // Assert redirect back with success message
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Party affiliation updated successfully.');

        // Assert database was updated
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'party_affiliation' => 'democrat',
            'party_assigned_by' => $admin->id,
        ]);

        // Assert timestamp was set
        $application->refresh();
        $this->assertNotNull($application->party_assigned_at);
    }

    /**
     * Test that admin can change party affiliation after initial assignment.
     */
    public function test_admin_can_change_party_affiliation_after_initial_assignment(): void
    {
        // Create admin users
        $admin1 = User::factory()->create(['is_admin' => true, 'name' => 'Admin One']);
        $admin2 = User::factory()->create(['is_admin' => true, 'name' => 'Admin Two']);

        // Create application with party affiliation
        $application = Application::factory()->create([
            'party_affiliation' => 'democrat',
            'party_assigned_at' => now()->subDay(),
            'party_assigned_by' => $admin1->id,
        ]);

        // Act as different admin and change party
        $response = $this->actingAs($admin2)->post(
            route('admin.applications.updateParty', $application->id),
            ['party_affiliation' => 'republican']
        );

        // Assert redirect back with success message
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Party affiliation updated successfully.');

        // Assert database was updated with new admin
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'party_affiliation' => 'republican',
            'party_assigned_by' => $admin2->id,
        ]);

        // Assert timestamp was updated
        $application->refresh();
        $this->assertTrue($application->party_assigned_at->isToday());
    }

    /**
     * Test that party update requires valid party value.
     */
    public function test_party_update_requires_valid_party(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create application
        $application = Application::factory()->create();

        // Try to update with invalid party
        $response = $this->actingAs($admin)->post(
            route('admin.applications.updateParty', $application->id),
            ['party_affiliation' => 'invalid']
        );

        // Assert validation error
        $response->assertSessionHasErrors('party_affiliation');
    }

    /**
     * Test that all valid party values are accepted.
     */
    public function test_all_valid_party_values_are_accepted(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        $valid_parties = ['democrat', 'republican', 'independent', 'unaffiliated'];

        foreach ($valid_parties as $party) {
            // Create application
            $application = Application::factory()->create();

            // Act as admin and assign party
            $response = $this->actingAs($admin)->post(
                route('admin.applications.updateParty', $application->id),
                ['party_affiliation' => $party]
            );

            // Assert redirect back with success message
            $response->assertRedirect();
            $response->assertSessionHas('success', 'Party affiliation updated successfully.');

            // Assert database was updated
            $this->assertDatabaseHas('applications', [
                'id' => $application->id,
                'party_affiliation' => $party,
            ]);
        }
    }

    /**
     * Test that non-admin cannot update party affiliation.
     */
    public function test_non_admin_cannot_update_party_affiliation(): void
    {
        // Create regular user
        $user = User::factory()->create(['is_admin' => false]);

        // Create application
        $application = Application::factory()->create();

        // Try to update party as non-admin
        $response = $this->actingAs($user)->post(
            route('admin.applications.updateParty', $application->id),
            ['party_affiliation' => 'democrat']
        );

        // Assert access is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that guest cannot update party affiliation.
     */
    public function test_guest_cannot_update_party_affiliation(): void
    {
        // Create application
        $application = Application::factory()->create();

        // Try to update party without authentication
        $response = $this->post(
            route('admin.applications.updateParty', $application->id),
            ['party_affiliation' => 'democrat']
        );

        // Assert access is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that admin can resend verification email for unverified application.
     */
    public function test_admin_can_resend_verification_email(): void
    {
        // Create admin user
        $admin = User::factory()->create(['is_admin' => true]);

        // Create unverified application with old token
        $old_token = 'old_verification_token_12345';
        $application = Application::factory()->create([
            'email_verified_at' => null,
            'verification_token' => $old_token,
            'verification_token_expires_at' => now()->addHours(48),
        ]);

        // Act as admin and resend verification
        $response = $this->actingAs($admin)->post(
            route('admin.applications.resendVerification', $application->id)
        );

        // Assert redirect back with success message
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Verification email resent successfully.');

        // Assert database was updated with new token
        $application->refresh();
        $this->assertNotEquals($old_token, $application->verification_token);
        $this->assertNotNull($application->verification_token);
        $this->assertNotNull($application->verification_token_expires_at);
        $this->assertTrue($application->verification_token_expires_at->isFuture());
    }

    /**
     * Test that non-admin cannot resend verification email.
     */
    public function test_non_admin_cannot_resend_verification_email(): void
    {
        // Create regular user
        $user = User::factory()->create(['is_admin' => false]);

        // Create unverified application
        $application = Application::factory()->create([
            'email_verified_at' => null,
        ]);

        // Try to resend verification as non-admin
        $response = $this->actingAs($user)->post(
            route('admin.applications.resendVerification', $application->id)
        );

        // Assert access is forbidden
        $response->assertStatus(403);
    }

    /**
     * Test that guest cannot resend verification email.
     */
    public function test_guest_cannot_resend_verification_email(): void
    {
        // Create unverified application
        $application = Application::factory()->create([
            'email_verified_at' => null,
        ]);

        // Try to resend verification without authentication
        $response = $this->post(
            route('admin.applications.resendVerification', $application->id)
        );

        // Assert access is forbidden
        $response->assertStatus(403);
    }
}
