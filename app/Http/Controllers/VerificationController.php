<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function __construct(
        private ApplicationService $application_service
    ) {}

    /**
     * Verify email address using token.
     */
    public function verify(string $token): View|RedirectResponse
    {
        $result = $this->application_service->verifyEmail($token);

        if ($result) {
            return view('verification.success');
        }

        return view('verification.expired');
    }
}
