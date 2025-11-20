<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Application;
use Illuminate\Pagination\LengthAwarePaginator;

class ApplicationRepository
{
    /**
     * Create a new application record.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Application
    {
        return Application::create($data);
    }

    /**
     * Find application by ID with relationships.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findById(int $id): Application
    {
        return Application::with(['user', 'residencyValidator', 'partyAssigner'])
            ->findOrFail($id);
    }

    /**
     * Find application by verification token.
     */
    public function findByVerificationToken(string $token): ?Application
    {
        return Application::where('verification_token', $token)->first();
    }

    /**
     * Update application record.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function update(int $id, array $data): Application
    {
        $application = Application::findOrFail($id);
        $application->update($data);

        return $application->fresh();
    }

    /**
     * Delete application record.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function delete(int $id): bool
    {
        return Application::findOrFail($id)->delete();
    }

    /**
     * Get filtered and paginated applications.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getFiltered(array $filters): LengthAwarePaginator
    {
        $query = Application::with(['user', 'residencyValidator', 'partyAssigner']);

        // Search filter: name, email, or street address
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('street_address', 'like', "%{$search}%");
            });
        }

        // Residency status filter
        if (! empty($filters['residency_status'])) {
            $query->where('residency_status', $filters['residency_status']);
        }

        // Party affiliation filter
        if (! empty($filters['party_affiliation'])) {
            $query->where('party_affiliation', $filters['party_affiliation']);
        }

        // Email verification status filter
        if (isset($filters['email_verified'])) {
            if ($filters['email_verified'] === 'yes') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Count applications by residency status.
     */
    public function countByResidencyStatus(string $status): int
    {
        return Application::where('residency_status', $status)->count();
    }

    /**
     * Count verified applications awaiting residency approval.
     */
    public function countVerifiedAwaitingApproval(): int
    {
        return Application::whereNotNull('email_verified_at')
            ->where('residency_status', 'pending')
            ->count();
    }

    /**
     * Count approved residents without party assignment.
     */
    public function countApprovedWithoutParty(): int
    {
        return Application::where('residency_status', 'approved')
            ->whereNull('party_affiliation')
            ->count();
    }

    /**
     * Count total applications.
     */
    public function countTotal(): int
    {
        return Application::count();
    }

    /**
     * Get all applications for CSV export with relationships.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Application>
     */
    public function getAllForExport(): \Illuminate\Database\Eloquent\Collection
    {
        return Application::with(['residencyValidator', 'partyAssigner'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
