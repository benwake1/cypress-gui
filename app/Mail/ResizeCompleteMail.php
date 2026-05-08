<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ResizeCompleteMail extends Mailable
{
    public string $direction;

    public function __construct(
        public string $customerEmail,
        public string $fromPlan,
        public string $toPlan,
        public string $dashboardUrl,
    ) {
        $planOrder = ['starter' => 1, 'standard' => 2, 'pro' => 3, 'enterprise' => 4];
        $this->direction = ($planOrder[$toPlan] ?? 0) > ($planOrder[$fromPlan] ?? 0)
            ? 'upgraded'
            : 'downgraded';
    }

    public function envelope(): Envelope
    {
        $label = ucfirst($this->direction);
        return new Envelope(
            subject: "{$label} to " . ucfirst($this->toPlan) . ' — SignalDeck',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.transactional.resize-complete',
        );
    }
}
