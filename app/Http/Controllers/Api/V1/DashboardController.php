<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\TestRun;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $sevenDaysAgo  = Carbon::now()->subDays(7);

        $completedRuns = TestRun::where('created_at', '>=', $thirtyDaysAgo)
            ->whereIn('status', [TestRun::STATUS_PASSING, TestRun::STATUS_FAILED])
            ->get();

        $totalRuns  = $completedRuns->count();
        $passingRuns = $completedRuns->where('status', TestRun::STATUS_PASSING)->count();
        $passRate   = $totalRuns > 0 ? round(($passingRuns / $totalRuns) * 100, 1) : 0;

        $currentlyRunning = TestRun::whereIn('status', [
            TestRun::STATUS_PENDING,
            TestRun::STATUS_CLONING,
            TestRun::STATUS_INSTALLING,
            TestRun::STATUS_RUNNING,
        ])->count();

        $avgDuration = TestRun::where('created_at', '>=', $sevenDaysAgo)
            ->whereNotNull('duration_ms')
            ->avg('duration_ms');

        return response()->json([
            'pass_rate_30d'     => $passRate,
            'total_runs_30d'    => $totalRuns,
            'passing_runs_30d'  => $passingRuns,
            'failed_runs_30d'   => $totalRuns - $passingRuns,
            'currently_running' => $currentlyRunning,
            'avg_duration_7d_ms' => $avgDuration ? round($avgDuration) : null,
        ]);
    }
}
