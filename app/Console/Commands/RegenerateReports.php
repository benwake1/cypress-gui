<?php

namespace App\Console\Commands;

use App\Models\TestRun;
use App\Services\ReportGeneratorService;
use Illuminate\Console\Command;

class RegenerateReports extends Command
{
    protected $signature   = 'runs:regenerate-reports';
    protected $description = 'Regenerate HTML reports for all completed runs (moves them to private disk)';

    public function handle(ReportGeneratorService $reporter): int
    {
        $count = TestRun::whereIn('status', ['passing', 'failed', 'error'])->count();
        $this->info("Regenerating reports for {$count} run(s)...");

        TestRun::whereIn('status', ['passing', 'failed', 'error'])
            ->chunkById(50, function ($runs) use ($reporter) {
                foreach ($runs as $run) {
                    try {
                        $reporter->generateHtmlReport($run);
                        $this->line("  ✓ Run #{$run->id}");
                    } catch (\Throwable $e) {
                        $this->warn("  ✗ Run #{$run->id}: {$e->getMessage()}");
                    }
                }
            });

        $this->info('Done.');
        return self::SUCCESS;
    }
}
