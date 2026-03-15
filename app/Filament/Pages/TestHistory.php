<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TestRunResource;
use App\Models\Project;
use App\Models\TestResult;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class TestHistory extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Test History';
    protected static ?string $navigationGroup = 'Testing';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.test-history';

    public ?int $projectId = null;
    public ?string $specFile = null;
    public ?string $fullTitle = null;

    public function mount(): void
    {
        $this->projectId = request()->integer('project') ?: null;
        $this->specFile  = request()->string('spec') ? urldecode(request()->string('spec')) : null;
        $this->fullTitle = request()->string('title') ? urldecode(request()->string('title')) : null;
    }

    public function table(Table $table): Table
    {
        $query = TestResult::query()
            ->join('test_runs', 'test_runs.id', '=', 'test_results.test_run_id')
            ->where('test_runs.project_id', $this->projectId ?? 0)
            ->where('test_results.full_title', $this->fullTitle ?? '')
            ->where('test_results.spec_file', $this->specFile ?? '')
            ->whereIn('test_results.status', ['passed', 'failed'])
            ->select(
                'test_results.*',
                'test_runs.branch as run_branch',
                'test_runs.commit_sha as run_commit_sha',
            )
            ->latest('test_results.created_at');

        return $table
            ->query($query)
            ->columns([
                TextColumn::make('test_run_id')
                    ->label('Run')
                    ->formatStateUsing(fn ($state) => '#' . $state)
                    ->url(fn ($record) => TestRunResource::getUrl('view', ['record' => $record->test_run_id]))
                    ->color('info')
                    ->fontFamily('mono'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'passed' => 'success',
                        'failed' => 'danger',
                        default  => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => ucfirst($state)),
                TextColumn::make('run_branch')
                    ->label('Branch')
                    ->fontFamily('mono')
                    ->color('gray'),
                TextColumn::make('duration_ms')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => $state < 1000 ? $state . 'ms' : round($state / 1000, 2) . 's'),
                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(80)
                    ->placeholder('—')
                    ->color('danger')
                    ->fontFamily('mono')
                    ->tooltip(fn ($record) => $record->error_message),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('test_results.created_at', 'desc')
            ->paginated([25, 50]);
    }

    public function getSummary(): array
    {
        if (!$this->projectId || !$this->fullTitle || !$this->specFile) {
            return [];
        }

        $history = DB::table('test_results as tr')
            ->join('test_runs', 'test_runs.id', '=', 'tr.test_run_id')
            ->where('test_runs.project_id', $this->projectId)
            ->where('tr.full_title', $this->fullTitle)
            ->where('tr.spec_file', $this->specFile)
            ->whereIn('tr.status', ['passed', 'failed'])
            ->orderByDesc('tr.created_at')
            ->limit(50)
            ->get(['tr.status', 'tr.duration_ms'])
            ->toArray();

        if (empty($history)) {
            return [];
        }

        $total    = count($history);
        $passed   = count(array_filter($history, fn ($r) => $r->status === 'passed'));
        $failed   = $total - $passed;
        $passRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
        $avgMs    = (int) round(array_sum(array_column($history, 'duration_ms')) / $total);

        $streak = $maxStreak = 0;
        foreach ($history as $r) {
            if ($r->status === 'failed') {
                $streak++;
                $maxStreak = max($maxStreak, $streak);
            } else {
                $streak = 0;
            }
        }

        return [
            'total'      => $total,
            'passed'     => $passed,
            'failed'     => $failed,
            'pass_rate'  => $passRate,
            'avg_ms'     => $avgMs,
            'max_streak' => $maxStreak,
        ];
    }

    public function getProject(): ?Project
    {
        return $this->projectId ? Project::find($this->projectId) : null;
    }

    public function getTitle(): string
    {
        return $this->fullTitle
            ? 'Test History: ' . \Illuminate\Support\Str::limit($this->fullTitle, 60)
            : 'Test History';
    }
}
