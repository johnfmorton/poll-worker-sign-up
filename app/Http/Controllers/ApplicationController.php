<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\ApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(
        private ApplicationService $applicationService
    ) {}

    /**
     * Display the registration form.
     */
    public function create(): View
    {
        $registration_enabled = Setting::isRegistrationEnabled();

        return view('applications.create', compact('registration_enabled'));
    }

    /**
     * Store a newly created application.
     */
    public function store(Request $request): RedirectResponse
    {
        // Check if registration is enabled
        if (!Setting::isRegistrationEnabled()) {
            return redirect()
                ->route('applications.create')
                ->with('error', 'Registration is currently disabled. Please contact the registrar\'s office.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:applications,email',
            'street_address' => 'required|string|max:500',
        ]);

        $this->applicationService->createApplication($validated);

        return redirect()
            ->route('applications.create')
            ->with('success', 'Registration submitted! Please check your email to verify your address.');
    }
}
