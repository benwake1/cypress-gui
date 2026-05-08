<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class WelcomeMail extends Mailable
{
    public function __construct(
        public string $customerEmail,
        public string $dashboardUrl,
        public string $adminPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your SignalDeck instance is ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.transactional.welcome',
        );
    }
}
