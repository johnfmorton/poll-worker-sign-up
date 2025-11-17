<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
        return view('applications.create');
    }

    /**
     * Store a newly created application.
     */
    public function store(Request $request): RedirectResponse
    {
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
