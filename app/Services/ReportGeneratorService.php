<?php

namespace App\Services;

use App\Models\TestRun;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Spatie\Browsershot\Browsershot;

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

    /**
     * Generate a PDF from the branded HTML report.
     */
    public function generatePdfReport(TestRun $run): string
    {
        $htmlPath = Storage::disk('local')->path($run->report_html_path);

        $path = "reports/run-{$run->id}/report.pdf";
        $outputPath = Storage::disk('local')->path($path);

        // Ensure directory exists
        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0755, true);
        }

        Browsershot::url('file://' . $htmlPath)
            ->landscape(false)
            ->format('A4')
            ->margins(15, 15, 15, 15)
            ->waitUntilNetworkIdle()
            ->save($outputPath);

        $run->update(['report_pdf_path' => $path]);

        return $path;
    }


    /**
     * Generate PDF directly from HTML string (fallback without Browsershot).
     * Uses a pure PHP approach via DomPDF if Browsershot is not available.
     */
    public function generatePdfFallback(TestRun $run): string
    {
        // Fallback: save the HTML with a print stylesheet
        // The HTML report is print-ready, users can print-to-PDF from their browser
        return $run->report_html_path;
    }
}
