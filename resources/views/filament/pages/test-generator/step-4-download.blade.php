{{-- Step 4: Preview & Download --}}

<div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900 overflow-hidden">
    <div class="flex items-center gap-3 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <x-heroicon-o-archive-box-arrow-down class="h-5 w-5 text-primary-500" />
        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Review and download</h2>
    </div>

    <div class="px-6 py-6 space-y-6">

        {{-- Summary grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

            {{-- Framework & Platform --}}
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Framework & Platform</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Framework</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $this->getFrameworkLabel() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Platform</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $this->getPlatformLabel() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Output format</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white font-mono text-xs">
                            {{ $framework === 'cypress' ? '.cy.js' : '.spec.ts' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Configuration --}}
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Configuration</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-gray-600 dark:text-gray-400 shrink-0">Base URL</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white truncate text-right font-mono text-xs">{{ $baseUrl ?: '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm text-gray-600 dark:text-gray-400 shrink-0">Test email</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white truncate text-right">{{ $testEmail ?: '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Timeout</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $timeoutSeconds }}s</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Headless</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $headless ? 'Yes' : 'No' }}</span>
                    </div>
                    @if($framework === 'playwright')
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Workers / Retries</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $pwWorkers }} / {{ $pwRetries }}</span>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Scenarios --}}
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">
                Test Scenarios ({{ count($selectedScenarios) }})
            </h3>
            <div class="flex flex-wrap gap-2">
                @foreach($this->getScenarioOptions() as $key => $scenario)
                    @if(in_array($key, $selectedScenarios))
                        <span class="inline-flex items-center gap-1 rounded-full bg-primary-50 dark:bg-primary-900/30 px-3 py-1 text-xs font-medium text-primary-700 dark:text-primary-300">
                            <x-heroicon-s-check-circle class="w-3 h-3" />
                            {{ $scenario['label'] }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- What's inside the ZIP --}}
        <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 p-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">What's in the ZIP</h3>
            <div class="font-mono text-xs text-gray-600 dark:text-gray-400 space-y-0.5">
                @if($framework === 'cypress')
                    <p><span class="text-gray-400">📦</span> cypress-tests/</p>
                    <p class="pl-4"><span class="text-gray-400">├</span> cypress.config.js</p>
                    <p class="pl-4"><span class="text-gray-400">├</span> package.json</p>
                    <p class="pl-4"><span class="text-gray-400">├</span> cypress/support/</p>
                    <p class="pl-4"><span class="text-gray-400">├</span> cypress/fixtures/ <span class="text-gray-400 font-sans">(selectors + credentials)</span></p>
                    <p class="pl-4"><span class="text-gray-400">└</span> cypress/e2e/ <span class="text-gray-400 font-sans">({{ count($selectedScenarios) }} test {{ Str::plural('file', count($selectedScenarios)) }})</span></p>
                @else
                    <p><span class="text-gray-400">📦</span> playwright-tests/</p>
                    <p class="pl-4"><span class="text-gray-400">├</span> playwright.config.ts</p>
                    <p class="pl-4"><span class="text-gray-400">├</span> package.json</p>
                    <p class="pl-4"><span class="text-gray-400">└</span> tests/ <span class="text-gray-400 font-sans">({{ count($selectedScenarios) }} test {{ Str::plural('file', count($selectedScenarios)) }})</span></p>
                @endif
            </div>
        </div>

        {{-- Customisation disclaimer --}}
        <div class="rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-4 flex gap-3">
            <x-heroicon-o-wrench-screwdriver class="h-5 w-5 text-amber-500 shrink-0 mt-0.5" />
            <div class="text-sm text-amber-800 dark:text-amber-300 space-y-1">
                <p class="font-medium">These tests will need customisation</p>
                <p class="text-xs text-amber-700 dark:text-amber-400">
                    The generated suite is a <strong>starting point</strong>, not a production-ready test suite. Selectors, product paths, and test data are based on common patterns for your chosen platform and will need to be updated to match your specific store's DOM structure, product catalogue, and checkout flow. Customising the test suite is outside the scope of this generator — it is your responsibility to review, adapt, and validate each test file before connecting it to SignalDeck CI.
                </p>
            </div>
        </div>

        {{-- Next steps callout --}}
        <div class="rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-4 flex gap-3">
            <x-heroicon-o-information-circle class="h-5 w-5 text-blue-500 shrink-0 mt-0.5" />
            <div class="text-sm text-blue-800 dark:text-blue-300 space-y-3">
                <p class="font-medium">Next steps after downloading</p>

                <div class="space-y-1">
                    <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wide">1 — Set up locally</p>
                    <ol class="list-decimal list-inside space-y-1 text-blue-700 dark:text-blue-400 text-xs pl-1">
                        <li>Unzip and drop the folder into your repo root.</li>
                        <li>Run <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">npm install</code> inside the test folder.</li>
                        @if($framework === 'playwright')
                            <li>Run <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">npm run install:browsers</code> to download Playwright's browser binaries.</li>
                        @endif
                        <li>Update selectors, product URLs, and test data to match your store.</li>
                        <li>Review any commented-out sections (e.g. guest checkout order placement) before enabling them.</li>
                        <li>Run <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">{{ $framework === 'cypress' ? 'npm test' : 'npm test' }}</code> locally to confirm everything passes.</li>
                    </ol>
                </div>

                <div class="space-y-1">
                    <p class="text-xs font-semibold text-blue-700 dark:text-blue-300 uppercase tracking-wide">
                        2 — Connect to SignalDeck CI
                        <a href="https://docs.signaldeck.tech/docs/guides/projects-and-suites" target="_blank" class="normal-case font-normal ml-2 underline underline-offset-2 hover:text-blue-900 dark:hover:text-blue-200">docs ↗</a>
                    </p>
                    <ol class="list-decimal list-inside space-y-1 text-blue-700 dark:text-blue-400 text-xs pl-1">
                        <li>Go to <strong class="text-blue-800 dark:text-blue-200">Projects → New Project</strong> and create a project for your store.</li>
                        <li>Under <strong class="text-blue-800 dark:text-blue-200">Project Settings → Runner</strong>, set the framework to <strong class="text-blue-800 dark:text-blue-200">{{ $framework === 'cypress' ? 'Cypress' : 'Playwright' }}</strong> and the test directory to <code class="bg-blue-100 dark:bg-blue-900 px-1 rounded">{{ $framework === 'cypress' ? 'cypress/e2e' : 'tests/' }}</code>.</li>
                        <li>Add your store URL as the <strong class="text-blue-800 dark:text-blue-200">Base URL</strong> in the project configuration.</li>
                        <li>Trigger a test run from the dashboard or connect your CI pipeline using the SignalDeck API token shown in project settings.</li>
                        <li>Monitor results, screenshots, and failure reports from the <strong class="text-blue-800 dark:text-blue-200">Test Runs</strong> view.</li>
                    </ol>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Navigation --}}
<div class="flex justify-between mt-6">
    <x-filament::button color="gray" wire:click="previousStep" icon="heroicon-o-arrow-left">
        Back
    </x-filament::button>
    <x-filament::button
        wire:click="downloadZip"
        wire:loading.attr="disabled"
        wire:target="downloadZip"
        icon="heroicon-o-arrow-down-tray"
        icon-position="after"
    >
        <span wire:loading.remove wire:target="downloadZip">Download ZIP</span>
        <span wire:loading wire:target="downloadZip">Generating...</span>
    </x-filament::button>
</div>
