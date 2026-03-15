<?php

namespace App\Filament\Pages;

use App\Models\TestResult;
use App\Models\TestRun;
use Filament\Pages\Page;

class CompareRuns extends Page
{
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Compare Runs';
    protected static ?string $navigationGroup = 'Testing';
    // Hidden from nav — accessed via the "Compare" action on run detail
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.compare-runs';

    public ?int $runAId = null;
    public ?int $runBId = null;
    public string $changeFilter = 'all';

    public function mount(): void
    {
        $this->runAId = request()->integer('run_a') ?: null;
        $this->runBId = request()->integer('run_b') ?: null;
    }

    public function getRunA(): ?TestRun
    {
        return $this->runAId ? TestRun::with(['project'])->find($this->runAId) : null;
    }

    public function getRunB(): ?TestRun
    {
        return $this->runBId ? TestRun::with(['project'])->find($this->runBId) : null;
    }

    public function getComparison(): array
    {
        if (!$this->runAId || !$this->runBId) {
            return [];
        }

        $resultsA = TestResult::where('test_run_id', $this->runAId)
            ->whereIn('status', ['passed', 'failed'])
            ->get()
            ->keyBy('full_title');

        $resultsB = TestResult::where('test_run_id', $this->runBId)
            ->whereIn('status', ['passed', 'failed'])
            ->get()
            ->keyBy('full_title');

        $allTitles = $resultsA->keys()->merge($resultsB->keys())->unique();

        $rows = $allTitles->map(function ($title) use ($resultsA, $resultsB) {
            $a = $resultsA->get($title);
            $b = $resultsB->get($title);

            $change = match (true) {
                !$a && $b                                                   => 'added',
                $a && !$b                                                   => 'removed',
                $a->status === 'passed' && $b->status === 'failed'         => 'regression',
                $a->status === 'failed' && $b->status === 'passed'         => 'fixed',
                $a->status === 'failed' && $b->status === 'failed'         => 'still_failing',
                default                                                     => 'unchanged',
            };

            return [
                'full_title' => $title,
                'spec_file'  => ($a ?? $b)->spec_file,
                'status_a'   => $a?->status,
                'status_b'   => $b?->status,
                'change'     => $change,
            ];
        })->sortBy(fn ($r) => match ($r['change']) {
            'regression'    => 0,
            'still_failing' => 1,
            'fixed'         => 2,
            'added'         => 3,
            'removed'       => 4,
            default         => 5,
        })->values()->toArray();

        if ($this->changeFilter !== 'all') {
            $rows = array_values(array_filter($rows, fn ($r) => $r['change'] === $this->changeFilter));
        }

        return $rows;
    }

    public function getSummary(): array
    {
        $all = $this->getComparison();

        return [
            'regressions'    => count(array_filter($all, fn ($r) => $r['change'] === 'regression')),
            'fixed'          => count(array_filter($all, fn ($r) => $r['change'] === 'fixed')),
            'still_failing'  => count(array_filter($all, fn ($r) => $r['change'] === 'still_failing')),
            'unchanged'      => count(array_filter($all, fn ($r) => $r['change'] === 'unchanged')),
            'added'          => count(array_filter($all, fn ($r) => $r['change'] === 'added')),
            'removed'        => count(array_filter($all, fn ($r) => $r['change'] === 'removed')),
        ];
    }

    public function setFilter(string $filter): void
    {
        $this->changeFilter = $filter;
    }

    public function getTitle(): string
    {
        return "Compare Runs #{$this->runAId} vs #{$this->runBId}";
    }
}
