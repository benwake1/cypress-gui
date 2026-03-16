<x-filament-panels::page>
    @php
        $runA      = $this->getRunA();
        $runB      = $this->getRunB();
        $summary   = $this->getSummary();
        $rows      = $this->getComparison();
    @endphp

    @if(!$runA || !$runB)
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
            <p class="text-gray-500">Two valid completed runs are required to compare. Use the Compare action on a test run.</p>
        </div>
    @else
        {{-- Run header --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
            @foreach([['run' => $runA, 'label' => 'Run A'], ['run' => $runB, 'label' => 'Run B']] as $side)
                @php $run = $side['run']; @endphp
                <div class="rounded-xl border p-4 bg-white dark:bg-gray-800 flex items-center justify-between gap-3">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ $side['label'] }}</p>
                        <a href="{{ \App\Filament\Resources\TestRunResource::getUrl('view', ['record' => $run->id]) }}"
                           class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">#{{ $run->id }}</a>
                        <span class="text-sm text-gray-500 ml-2">{{ $run->project->name }}</span>
                    </div>
                    <div class="text-right text-xs text-gray-500">
                        <p class="font-mono">{{ $run->branch }}</p>
                        <p>{{ $run->created_at->diffForHumans() }}</p>
                    </div>
                    <div>
                        @php
                            $badgeClass = match($run->status) {
                                'passing'  => 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300',
                                'failed'   => 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300',
                                default    => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">{{ $run->status }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Summary diff --}}
        <div class="flex flex-wrap gap-3 mb-2">
            @foreach([
                ['key' => 'all',           'label' => 'All',             'count' => array_sum($summary), 'class' => 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white'],
                ['key' => 'regression',    'label' => '🔴 Regressions',  'count' => $summary['regressions'], 'class' => 'bg-red-100 dark:bg-red-700 text-red-800 dark:text-white'],
                ['key' => 'fixed',         'label' => '🟢 Fixed',         'count' => $summary['fixed'], 'class' => 'bg-green-100 dark:bg-green-700 text-green-800 dark:text-white'],
                ['key' => 'still_failing', 'label' => '🟡 Still failing', 'count' => $summary['still_failing'], 'class' => 'bg-amber-100 dark:bg-amber-700 text-amber-800 dark:text-white'],
                ['key' => 'unchanged',     'label' => 'Unchanged',       'count' => $summary['unchanged'], 'class' => 'bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white'],
            ] as $filter)
                <button
                    wire:click="setFilter('{{ $filter['key'] }}')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $filter['class'] }} {{ $changeFilter === $filter['key'] ? 'ring-2 ring-offset-1 ring-current' : 'opacity-80 hover:opacity-100' }}"
                >
                    {{ $filter['label'] }}
                    <span class="ml-1 font-bold">{{ $filter['count'] }}</span>
                </button>
            @endforeach
        </div>

        {{-- Comparison table --}}
        @if(empty($rows))
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                <p class="text-gray-500">No tests match the selected filter.</p>
            </div>
        @else
            {{-- Use Filament's fi-ta-* CSS classes so dark mode is handled by Filament's own stylesheet --}}
            <div class="fi-ta-ctn rounded-xl overflow-hidden shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 text-start dark:divide-white/5">
                    <thead class="divide-y divide-gray-200 dark:divide-white/5">
                        <tr>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-left text-sm font-semibold text-gray-950 dark:text-white">Test</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-center text-sm font-semibold text-gray-950 dark:text-white hidden sm:table-cell">Run A</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-center text-sm font-semibold text-gray-950 dark:text-white hidden sm:table-cell">Run B</th>
                            <th class="fi-ta-header-cell px-3 py-3.5 text-center text-sm font-semibold text-gray-950 dark:text-white">Change</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        @foreach($rows as $row)
                            @php
                                $rowBg = match($row['change']) {
                                    'regression'    => 'bg-red-50 dark:bg-red-950',
                                    'fixed'         => 'bg-green-50 dark:bg-green-950',
                                    'still_failing' => 'bg-amber-50 dark:bg-amber-950',
                                    default         => '',
                                };
                                $changeBadge = match($row['change']) {
                                    'regression'    => ['label' => '🔴 Regression',    'class' => 'bg-red-100 dark:bg-red-700 text-red-800 dark:text-white'],
                                    'fixed'         => ['label' => '🟢 Fixed',          'class' => 'bg-green-100 dark:bg-green-700 text-green-800 dark:text-white'],
                                    'still_failing' => ['label' => '🟡 Still failing',  'class' => 'bg-amber-100 dark:bg-amber-700 text-amber-800 dark:text-white'],
                                    'added'         => ['label' => '➕ Added',           'class' => 'bg-blue-100 dark:bg-blue-700 text-blue-800 dark:text-white'],
                                    'removed'       => ['label' => '➖ Removed',         'class' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white'],
                                    default         => ['label' => '— Unchanged',       'class' => 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-white'],
                                };
                                $statusIcon = fn($s) => match($s) { 'passed' => '✅', 'failed' => '❌', default => '—' };
                            @endphp
                            <tr class="fi-ta-row hover:bg-gray-50 dark:hover:bg-white/5 transition-colors {{ $rowBg }}">
                                <td class="fi-ta-cell px-3 py-4 whitespace-normal">
                                    <p class="text-sm font-medium text-gray-950 dark:text-white">{{ $row['full_title'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">{{ basename($row['spec_file']) }}</p>
                                </td>
                                <td class="fi-ta-cell px-3 py-4 text-center text-sm hidden sm:table-cell">
                                    {{ $statusIcon($row['status_a']) }}
                                </td>
                                <td class="fi-ta-cell px-3 py-4 text-center text-sm hidden sm:table-cell">
                                    {{ $statusIcon($row['status_b']) }}
                                </td>
                                <td class="fi-ta-cell px-3 py-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $changeBadge['class'] }}">
                                        {{ $changeBadge['label'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</x-filament-panels::page>
