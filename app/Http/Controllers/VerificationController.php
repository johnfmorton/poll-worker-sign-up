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

        if ($result['success']) {
            return view('verification.success');
        }

        return view('verification.expired', [
            'already_verified' => $result['already_verified'] ?? false,
            'email' => $result['email'] ?? null,
        ]);
    }

    /**
     * Resend verification email from expired page.
     */
    public function resend(string $email): RedirectResponse
    {
        $this->application_service->resendVerificationEmailByEmail($email);

        return redirect()
            ->route('verification.expired')
            ->with('success', 'A new verification email has been sent to your email address.');
    }
}
