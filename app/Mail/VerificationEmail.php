<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Application $application
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify Your Poll Worker Registration',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $verification_url = route('verification.verify', [
            'token' => $this->application->verification_token,
        ]);

        return new Content(
            view: 'emails.verification',
            with: [
                'name' => $this->application->name,
                'verification_url' => $verification_url,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
