<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Mail;

use App\Models\AppSetting;
use App\Models\TestSuite;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SuiteHealthBreachedMailable extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly TestSuite $suite,
        public readonly float     $currentPassRate,
        public readonly float     $threshold
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = AppSetting::get('mail_from_address') ?: config('mail.from.address');
        $fromName    = AppSetting::get('mail_from_name') ?: config('mail.from.name');

        return new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: sprintf(
                '⚠️ Health Breach — %s / %s (%.1f%% < %.0f%%)',
                $this->suite->project->name ?? 'Unknown',
                $this->suite->name,
                $this->currentPassRate,
                $this->threshold
            ),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.suite-health-breached');
    }
}
