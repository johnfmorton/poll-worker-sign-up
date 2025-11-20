<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Application;
use App\Models\User;
use App\Repositories\ApplicationRepository;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationService
{
    public function __construct(
        private ApplicationRepository $application_repository,
        private EmailService $email_service
    ) {}

    /**
     * Create a new application with verification token and send email.
     *
     * @param  array{name: string, email: string, street_address: string}  $data
     */
    public function createApplication(array $data): Application
    {
        return DB::transaction(function () use ($data) {
            $application = $this->application_repository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'street_address' => $data['street_address'],
                'verification_token' => Str::random(64),
                'verification_token_expires_at' => Carbon::now()->addHours(48),
                'residency_status' => 'pending',
            ]);

            $this->email_service->sendVerificationEmail($application);

            return $application;
        });
    }

    /**
     * Verify email using token and create user account.
     */
    public function verifyEmail(string $token): bool
    {
        $application = $this->application_repository->findByVerificationToken($token);

        if (! $application || $application->verification_token_expires_at < Carbon::now()) {
            return false;
        }

        return DB::transaction(function () use ($application) {
            // Create user account
            $user = User::create([
                'name' => $application->name,
                'email' => $application->email,
                'password' => Hash::make(Str::random(32)), // Random password, user won't log in
                'is_admin' => false,
            ]);

            // Update application
            $this->application_repository->update($application->id, [
                'email_verified_at' => Carbon::now(),
                'verification_token' => null,
                'verification_token_expires_at' => null,
                'user_id' => $user->id,
            ]);

            return true;
        });
    }

    /**
     * Get filtered applications with pagination.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFilteredApplications(array $filters): LengthAwarePaginator
    {
        return $this->application_repository->getFiltered($filters);
    }

    /**
     * Get application by ID.
     */
    public function getApplicationById(int $id): Application
    {
        return $this->application_repository->findById($id);
    }

    /**
     * Update application data.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateApplication(int $id, array $data): Application
    {
        return $this->application_repository->update($id, $data);
    }

    /**
     * Delete application and associated user account.
     */
    public function deleteApplication(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $application = $this->application_repository->findById($id);

            // Delete associated user if exists
            if ($application->user_id) {
                $application->user()->delete();
            }

            return $this->application_repository->delete($id);
        });
    }

    /**
     * Update residency status with validator information.
     */
    public function updateResidencyStatus(int $id, string $status, int $validator_id): Application
    {
        return $this->application_repository->update($id, [
            'residency_status' => $status,
            'residency_validated_at' => Carbon::now(),
            'residency_validated_by' => $validator_id,
        ]);
    }

    /**
     * Update party affiliation with assigner information.
     */
    public function updatePartyAffiliation(int $id, string $party, int $assigner_id): Application
    {
        return $this->application_repository->update($id, [
            'party_affiliation' => $party,
            'party_assigned_at' => Carbon::now(),
            'party_assigned_by' => $assigner_id,
        ]);
    }

    /**
     * Resend verification email with new token.
     */
    public function resendVerificationEmail(int $id): void
    {
        $application = $this->application_repository->findById($id);

        // Generate new token and invalidate old one
        $this->application_repository->update($id, [
            'verification_token' => Str::random(64),
            'verification_token_expires_at' => Carbon::now()->addHours(48),
        ]);

        $application->refresh();
        $this->email_service->sendVerificationEmail($application);
    }

    /**
     * Get dashboard statistics for admin overview.
     *
     * @return array<string, int>
     */
    public function getDashboardStats(): array
    {
        return [
            'pending_residency' => $this->application_repository->countByResidencyStatus('pending'),
            'verified_awaiting_approval' => $this->application_repository->countVerifiedAwaitingApproval(),
            'approved_no_party' => $this->application_repository->countApprovedWithoutParty(),
            'total_applications' => $this->application_repository->countTotal(),
        ];
    }

    /**
     * Export all applications to CSV format.
     */
    public function exportApplicationsToCSV(): StreamedResponse
    {
        $applications = $this->application_repository->getAllForExport();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="poll-workers-'.date('Y-m-d').'.csv"',
        ];

        $callback = function () use ($applications) {
            $file = fopen('php://output', 'w');

            // CSV header row
            fputcsv($file, [
                'ID',
                'Name',
                'Email',
                'Street Address',
                'Email Verified',
                'Email Verified At',
                'Residency Status',
                'Residency Validated At',
                'Residency Validated By',
                'Party Affiliation',
                'Party Assigned At',
                'Party Assigned By',
                'Created At',
                'Updated At',
            ]);

            // Data rows
            foreach ($applications as $app) {
                fputcsv($file, [
                    $app->id,
                    $app->name,
                    $app->email,
                    $app->street_address,
                    $app->email_verified_at ? 'Yes' : 'No',
                    $app->email_verified_at?->format('Y-m-d H:i:s'),
                    $app->residency_status,
                    $app->residency_validated_at?->format('Y-m-d H:i:s'),
                    $app->residencyValidator?->name,
                    $app->party_affiliation,
                    $app->party_assigned_at?->format('Y-m-d H:i:s'),
                    $app->partyAssigner?->name,
                    $app->created_at->format('Y-m-d H:i:s'),
                    $app->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
