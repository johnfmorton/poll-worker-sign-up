<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(
        private ApplicationService $application_service
    ) {}

    /**
     * Display a listing of applications with filters.
     */
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'residency_status', 'party_affiliation', 'email_verified']);
        $applications = $this->application_service->getFilteredApplications($filters);

        return view('admin.applications.index', compact('applications', 'filters'));
    }

    /**
     * Display the specified application with full details.
     */
    public function show(int $id): View
    {
        $application = $this->application_service->getApplicationById($id);

        return view('admin.applications.show', compact('application'));
    }

    /**
     * Show the form for editing the specified application.
     */
    public function edit(int $id): View
    {
        $application = $this->application_service->getApplicationById($id);

        return view('admin.applications.edit', compact('application'));
    }

    /**
     * Update the specified application in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'street_address' => 'required|string|max:500',
        ]);

        $this->application_service->updateApplication($id, $validated);

        return redirect()
            ->route('admin.applications.show', $id)
            ->with('success', 'Application updated successfully.');
    }

    /**
     * Remove the specified application from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $this->application_service->deleteApplication($id);

        return redirect()
            ->route('admin.applications.index')
            ->with('success', 'Application deleted successfully.');
    }

    /**
     * Update the residency status of the application.
     */
    public function updateResidency(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'residency_status' => 'required|in:approved,rejected',
        ]);

        $this->application_service->updateResidencyStatus(
            $id,
            $validated['residency_status'],
            $request->user()->id
        );

        return back()->with('success', 'Residency status updated successfully.');
    }

    /**
     * Update the party affiliation of the application.
     */
    public function updateParty(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'party_affiliation' => 'required|in:democrat,republican,independent,unaffiliated',
        ]);

        $this->application_service->updatePartyAffiliation(
            $id,
            $validated['party_affiliation'],
            $request->user()->id
        );

        return back()->with('success', 'Party affiliation updated successfully.');
    }

    /**
     * Resend the verification email for the application.
     */
    public function resendVerification(int $id): RedirectResponse
    {
        $this->application_service->resendVerificationEmail($id);

        return back()->with('success', 'Verification email resent successfully.');
    }
}
