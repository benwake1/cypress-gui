<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Listeners;

use App\Events\SuiteHealthBreached;
use App\Models\AppSetting;
use App\Services\SlackService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSuiteHealthBreachSlack implements ShouldQueue
{
    public function __construct(private SlackService $slack) {}

    public function handle(SuiteHealthBreached $event): void
    {
        if (!$this->slack->isEnabled()) {
            return;
        }

        // Breach alerts go to a configured channel, not a user DM.
        // If no breach channel is set, skip silently.
        $channelId = AppSetting::get('slack_breach_channel');
        if (!$channelId) {
            return;
        }

        $suite   = $event->suite;
        $project = $suite->project->name ?? '—';
        $client  = $suite->project->client->name ?? '—';

        $blocks = [
            [
                'type' => 'header',
                'text' => ['type' => 'plain_text', 'text' => '⚠️ Suite Health Breach', 'emoji' => true],
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => sprintf(
                        "*%s / %s*\nSuite: *%s*\n\nPass rate: *%.1f%%* is below threshold of *%.0f%%*",
                        $client,
                        $project,
                        $suite->name,
                        $event->currentPassRate,
                        $event->threshold
                    ),
                ],
            ],
        ];

        // sendDm() calls chat.postMessage — the channel field accepts channel IDs as well as user IDs
        $this->slack->sendDm($channelId, $blocks);
    }
}
