<?php

namespace App\Filament\Pages;

use App\Models\Project;
use App\Models\TestResult;
use Filament\Pages\Page;
use Filament\Support\Enums\ActionSize;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class FlakyTests extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = 'Flaky Tests';
    protected static ?string $navigationGroup = 'Testing';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.flaky-tests';

    public ?int $projectFilter = null;
    public int $minScore = 0;

    // Used to bust the table cache when filters change
    public string $tableFiltersKey = '';

    private string $flakyScoreSql = "ROUND((CASE WHEN SUM(CASE WHEN test_results.status = 'passed' THEN 1 ELSE 0 END) <= SUM(CASE WHEN test_results.status = 'failed' THEN 1 ELSE 0 END) THEN SUM(CASE WHEN test_results.status = 'passed' THEN 1 ELSE 0 END) ELSE SUM(CASE WHEN test_results.status = 'failed' THEN 1 ELSE 0 END) END / CAST(COUNT(*) AS FLOAT)) * 100, 1)";

    public function mount(): void
    {
        $this->projectFilter = request()->integer('project') ?: null;
    }

    public function updatedProjectFilter(): void
    {
        $this->resetTable();
    }

    public function updatedMinScore(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $sql = $this->flakyScoreSql;

        $query = TestResult::query()
            ->join('test_runs', 'test_runs.id', '=', 'test_results.test_run_id')
            ->join('projects', 'projects.id', '=', 'test_runs.project_id')
            ->whereIn('test_results.status', ['passed', 'failed'])
            ->select([
                DB::raw('MAX(test_results.id) as id'),
                'test_results.spec_file',
                'test_results.full_title',
                DB::raw('projects.name as project_name'),
                DB::raw('projects.id as project_id'),
                DB::raw('COUNT(*) as run_count'),
                DB::raw("SUM(CASE WHEN test_results.status = 'passed' THEN 1 ELSE 0 END) as pass_count"),
                DB::raw("SUM(CASE WHEN test_results.status = 'failed' THEN 1 ELSE 0 END) as fail_count"),
                DB::raw("{$sql} as flaky_score"),
                DB::raw('MAX(test_results.created_at) as last_seen'),
            ])
            ->groupBy('test_results.spec_file', 'test_results.full_title', 'projects.id', 'projects.name')
            ->havingRaw('COUNT(*) >= 3')
            ->havingRaw("SUM(CASE WHEN test_results.status = 'passed' THEN 1 ELSE 0 END) > 0")
            ->havingRaw("SUM(CASE WHEN test_results.status = 'failed' THEN 1 ELSE 0 END) > 0");

        if ($this->projectFilter) {
            $query->where('projects.id', $this->projectFilter);
        }

        if ($this->minScore > 0) {
            $query->havingRaw("{$sql} >= ?", [$this->minScore]);
        }

        return $table
            ->query($query)
            ->defaultSort('flaky_score', 'desc')
            ->columns([
                TextColumn::make('full_title')
                    ->label('Test')
                    ->description(fn ($record) => basename($record->spec_file))
                    ->wrap()
                    ->searchable(),
                TextColumn::make('project_name')
                    ->label('Project')
                    ->badge()
                    ->color('info'),
                TextColumn::make('flaky_score')
                    ->label('Flakiness')
                    ->badge()
                    ->color(fn ($state) => (float) $state >= 40 ? 'danger' : ((float) $state >= 20 ? 'warning' : 'info'))
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable(),
                TextColumn::make('pass_count')
                    ->label('Pass Rate')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => $record->run_count > 0
                        ? round(($state / $record->run_count) * 100) . '%'
                        : '0%')
                    ->color(fn ($state, $record) => match(true) {
                        $record->run_count > 0 && round(($state / $record->run_count) * 100) >= 80 => 'success',
                        $record->run_count > 0 && round(($state / $record->run_count) * 100) >= 50 => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('recent_runs')
                    ->label('Last 10 Runs')
                    ->state(fn ($record) => $this->buildDotsHtml(
                        $record->full_title,
                        $record->spec_file,
                        $record->project_id
                    ))
                    ->html(),
                TextColumn::make('last_seen')
                    ->label('Last Seen')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Action::make('history')
                    ->label('History')
                    ->url(fn ($record) => TestHistory::getUrl([
                        'project' => $record->project_id,
                        'spec'    => urlencode($record->spec_file),
                        'title'   => urlencode($record->full_title),
                    ]))
                    ->button()
                    ->size(ActionSize::Small)
                    ->color('info')
                    ->extraAttributes(['class' => '!rounded-full']),
            ])
            ->emptyStateHeading('No flaky tests detected 🎉')
            ->emptyStateDescription('Tests need at least 3 runs with mixed pass/fail results to be flagged as flaky.')
            ->paginated(false);
    }

    private function buildDotsHtml(string $fullTitle, string $specFile, int $projectId): string
    {
        $results = DB::table('test_results as tr')
            ->join('test_runs', 'test_runs.id', '=', 'tr.test_run_id')
            ->where('test_runs.project_id', $projectId)
            ->where('tr.full_title', $fullTitle)
            ->where('tr.spec_file', $specFile)
            ->whereIn('tr.status', ['passed', 'failed'])
            ->orderByDesc('tr.created_at')
            ->limit(10)
            ->pluck('tr.status')
            ->toArray();

        $html = '<div class="flex items-center gap-0.5">';
        foreach (array_reverse($results) as $status) {
            $color = $status === 'passed' ? 'bg-green-400' : 'bg-red-400';
            $html .= '<span class="inline-block w-3 h-3 rounded-full ' . $color . '" title="' . $status . '"></span>';
        }
        $html .= '</div>';

        return $html;
    }

    public function getProjects(): array
    {
        return Project::orderBy('name')->pluck('name', 'id')->toArray();
    }
}
