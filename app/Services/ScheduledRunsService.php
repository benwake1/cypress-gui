<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Services;

use App\Models\TestSuite;
use Carbon\Carbon;
use Cron\CronExpression;
use Illuminate\Support\Facades\Cache;

class ScheduledRunsService
{
    /**
     * Count the total number of scheduled suite runs due to fire between now and midnight.
     * Result is cached for 60 seconds to avoid recalculating on every dashboard load.
     */
    public static function countForToday(): int
    {
        return Cache::remember('scheduled_today_count', 60, function () {
            $suites = TestSuite::where('schedule_enabled', true)
                ->whereNotNull('schedule_cron')
                ->where('active', true)
                ->with('project')
                ->get()
                ->filter(fn ($suite) => $suite->project?->active);

            $count = 0;

            foreach ($suites as $suite) {
                try {
                    $tz   = $suite->schedule_timezone ?? config('app.timezone');
                    $cron = new CronExpression($suite->schedule_cron);
                } catch (\Throwable $e) {
                    continue;
                }

                $cursor   = Carbon::now($tz)->second(0);
                $endOfDay = Carbon::now($tz)->endOfDay();

                while ($cursor->lessThanOrEqualTo($endOfDay)) {
                    if ($cron->isDue($cursor->format('Y-m-d H:i:s'))) {
                        $count++;
                    }
                    $cursor->addMinute();
                }
            }

            return $count;
        });
    }
}
