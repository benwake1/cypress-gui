<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Listeners;

use App\Events\TestRunStatusChanged;
use App\Services\SlackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;

class SendTestRunSlackNotification implements ShouldQueue
{
    public function __construct(private SlackService $slack) {}

    public function handle(TestRunStatusChanged $event): void
    {
        $run = $event->run;

        if (! in_array($event->status, ['passing', 'failed'])) {
            return;
        }

        if (! $this->slack->isEnabled()) {
            return;
        }

        $user = $run->triggeredBy;
        if (! $user) {
            return;
        }

        // Resolve Slack user ID: manual override takes priority, then email lookup
        $slackUserId = $user->slack_user_id
            ?: $this->slack->getUserIdByEmail($user->email);

        if (! $slackUserId) {
            return;
        }

        // Deduplicate — one DM per run
        $cacheKey = "slack_dm_sent_run_{$run->id}";
        if (! Cache::add($cacheKey, true, now()->addMinutes(10))) {
            return;
        }

        $this->slack->sendDm($slackUserId, $this->buildBlocks($event));
    }

    private function buildBlocks(TestRunStatusChanged $event): array
    {
        $run       = $event->run;
        $isPassing = $event->status === 'passing';
        $status    = $isPassing ? '✅ All Tests Passed' : '❌ Tests Failed';
        $client    = $run->project->client->name ?? '—';
        $project   = $run->project->name ?? '—';
        $suite     = $run->testSuite->name ?? '—';
        $branch    = $run->branch ?? '—';
        $commit    = $run->commit_sha ? '`' . substr($run->commit_sha, 0, 8) . '`' : null;
        $passed    = $run->passed_tests ?? 0;
        $failed    = $run->failed_tests ?? 0;
        $total     = $run->total_tests ?? 0;
        $passRate  = $run->pass_rate ?? 0;
        $duration  = $run->duration_formatted ?? '—';
        $completed = $run->finished_at?->format('d M Y, H:i') ?? '—';

        $metaLine = "Suite: {$suite}  ·  Branch: `{$branch}`"
            . ($commit ? "  ·  Commit: {$commit}" : '');

        $statsLine = "✅ Passed: *{$passed}*   ❌ Failed: *{$failed}*   📊 Total: *{$total}*   🎯 Pass Rate: *{$passRate}%*";

        $timeLine = "⏱ Duration: {$duration}   🕐 Completed: {$completed}";

        $blocks = [
            [
                'type' => 'header',
                'text' => ['type' => 'plain_text', 'text' => $status, 'emoji' => true],
            ],
            [
                'type' => 'section',
                'text' => [
                    'type' => 'mrkdwn',
                    'text' => "*{$client} / {$project}*\n{$metaLine}\n\n{$statsLine}\n\n{$timeLine}",
                ],
            ],
        ];

        // Add action buttons if we have a shareable URL
        $shareUrl     = $run->report_share_url;
        $dashboardUrl = $run->report_html_url;

        if ($shareUrl || $dashboardUrl) {
            $elements = [];

            if ($shareUrl) {
                $elements[] = [
                    'type'  => 'button',
                    'text'  => ['type' => 'plain_text', 'text' => 'View Full Report', 'emoji' => true],
                    'url'   => $shareUrl,
                    'style' => 'primary',
                ];
            }

            if ($dashboardUrl) {
                $elements[] = [
                    'type' => 'button',
                    'text' => ['type' => 'plain_text', 'text' => 'Open in Dashboard', 'emoji' => true],
                    'url'  => $dashboardUrl,
                ];
            }

            $blocks[] = ['type' => 'actions', 'elements' => $elements];
        }

        $blocks[] = [
            'type'     => 'context',
            'elements' => [[
                'type' => 'mrkdwn',
                'text' => 'The report link is valid for 30 days and does not require a login.',
            ]],
        ];

        return $blocks;
    }
}
