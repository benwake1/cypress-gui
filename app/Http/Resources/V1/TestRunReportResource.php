<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestRunReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $testRun = $this->resource;
        $project = $testRun->project;
        $suite = $testRun->testSuite;
        $triggeredBy = $testRun->triggeredBy;
        $client = $project?->client;

        $resultsBySpec = collect($testRun->testResults)
            ->groupBy('spec_file')
            ->map(function ($results, $specFile) {
                return [
                    'spec_file' => $specFile,
                    'tests' => $results->map(fn ($result) => [
                        'test_title' => $result->test_title,
                        'full_title' => $result->full_title,
                        'status' => $result->status,
                        'duration_ms' => $result->duration_ms,
                        'duration_formatted' => $result->duration_formatted,
                        'error_message' => $result->error_message,
                        'error_stack' => $result->error_stack,
                        'screenshot_urls' => $result->screenshot_urls,
                        'video_url' => $result->video_url,
                        'attempt' => $result->attempt,
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();

        return [
            'summary' => [
                'total_tests' => $testRun->total_tests,
                'passed_tests' => $testRun->passed_tests,
                'failed_tests' => $testRun->failed_tests,
                'pending_tests' => $testRun->pending_tests,
                'duration_ms' => $testRun->duration_ms,
                'duration_formatted' => $testRun->duration_formatted,
                'pass_rate' => $testRun->pass_rate,
                'status' => $testRun->status,
                'started_at' => $testRun->started_at,
                'finished_at' => $testRun->finished_at,
            ],
            'project' => [
                'name' => $project?->name,
                'runner_type' => $project?->runner_type,
            ],
            'suite' => [
                'name' => $suite?->name,
            ],
            'triggered_by' => [
                'name' => $triggeredBy?->name,
                'email' => $triggeredBy?->email,
            ],
            'client' => [
                'name' => $client?->name,
                'logo_url' => $client?->logo_url,
                'primary_colour' => $client?->primary_colour,
                'secondary_colour' => $client?->secondary_colour,
                'accent_colour' => $client?->accent_colour,
            ],
            'results' => $resultsBySpec,
        ];
    }
}
