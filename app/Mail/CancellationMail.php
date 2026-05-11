<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CancellationMail extends Mailable
{
    public function __construct(
        public string $customerEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your SignalDeck CI subscription has been cancelled',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.transactional.cancellation',
        );
    }
}
