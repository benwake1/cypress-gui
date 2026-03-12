<?php

namespace App\Services;

use App\Models\TestRun;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class ReportGeneratorService
{
    /**
     * Generate a branded HTML report and store it.
     */
    public function generateHtmlReport(TestRun $run): string
    {
        $run->load([
            'project.client',
            'testSuite',
            'testResults',
            'triggeredBy',
        ]);

        $html = View::make('reports.branded', [
            'run' => $run,
            'project' => $run->project,
            'client' => $run->project->client,
            'suite' => $run->testSuite,
            'results' => $run->testResults,
            'failedResults' => $run->testResults->where('status', 'failed'),
            'passedResults' => $run->testResults->where('status', 'passed'),
            'resultsBySpec' => $run->testResults->groupBy('spec_file'),
            'generatedAt' => now(),
        ])->render();

        $path = "reports/run-{$run->id}/report.html";
        Storage::disk('local')->put($path, $html);

        $run->update(['report_html_path' => $path]);

        return $path;
    }

}
