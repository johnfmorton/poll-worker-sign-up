<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\VerificationEmail;
use App\Models\Application;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Send verification email to applicant.
     */
    public function sendVerificationEmail(Application $application): void
    {
        Mail::to($application->email)
            ->queue(new VerificationEmail($application));
    }
}
