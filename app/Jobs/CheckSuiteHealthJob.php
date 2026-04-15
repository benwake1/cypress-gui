<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Jobs;

use App\Events\SuiteHealthBreached;
use App\Models\TestRun;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckSuiteHealthJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly TestRun $run) {}

    public function handle(): void
    {
        $suite = $this->run->testSuite;

        if (!$suite || $suite->pass_rate_threshold === null) {
            return;
        }

        $healthScore = $suite->health_score;

        if ($healthScore >= $suite->pass_rate_threshold) {
            return;
        }

        // Cooldown — suppress duplicate alerts within 1 hour
        if ($suite->last_breach_at &&
            $suite->last_breach_at->isAfter(now()->subHour())) {
            return;
        }

        $suite->updateQuietly(['last_breach_at' => now()]);

        event(new SuiteHealthBreached($suite, $healthScore, $suite->pass_rate_threshold));
    }
}
