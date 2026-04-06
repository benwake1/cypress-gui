<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Filament\Pages;

use App\Models\Project;
use App\Services\TestGeneratorService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TestGeneratorPage extends Page
{
    protected static ?string $navigationIcon  = null;
    protected static ?string $navigationLabel = 'Test Generator';
    protected static ?string $navigationGroup = 'Testing';
    protected static ?int    $navigationSort  = 5;
    protected static string  $view            = 'filament.pages.test-generator';
    protected static ?string $title           = 'Test Suite Generator';
    protected static ?string $slug            = 'test-generator';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->isAdmin() || $user?->isPM();
    }

    // ── Step state ────────────────────────────────────────────────────────

    public int $currentStep = 1;

    // Step 1
    public string $framework          = 'cypress';
    public string $ecommercePlatform  = 'magento-hyva';
    public ?int   $projectId          = null;

    // Step 2
    public array $selectedScenarios = [];

    // Step 3
    public string $baseUrl        = '';
    public string $adminUrl       = '';
    public string $testEmail      = '';
    public string $testPassword   = '';
    public int    $timeoutSeconds = 30;
    public bool   $headless       = true;
    public int    $pwWorkers      = 4;
    public int    $pwRetries      = 2;

    // ── Mount ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        // Pre-fill base URL from project if linked in query string
        $projectId = request()->integer('project_id') ?: null;
        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $this->projectId        = $project->id;
                $this->framework        = $project->runner_type->value ?? 'cypress';
                $this->baseUrl          = '';
            }
        }
    }

    // ── Navigation ────────────────────────────────────────────────────────

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
            return;
        }

        // Validate each intermediate step
        for ($s = $this->currentStep; $s < $step; $s++) {
            if (! $this->validateStep($s)) {
                return;
            }
        }

        $this->currentStep = $step;
    }

    public function nextStep(): void
    {
        if (! $this->validateStep($this->currentStep)) {
            return;
        }
        $this->currentStep++;
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    // ── Validation ────────────────────────────────────────────────────────

    private function validateStep(int $step): bool
    {
        return match ($step) {
            1 => true,
            2 => $this->validateScenarioSelection(),
            3 => $this->validateConfiguration(),
            default => true,
        };
    }

    private function validateScenarioSelection(): bool
    {
        if (empty($this->selectedScenarios)) {
            Notification::make()
                ->title('Please select at least one test scenario')
                ->danger()
                ->send();
            return false;
        }
        return true;
    }

    private function validateConfiguration(): bool
    {
        $validator = Validator::make([
            'base_url'        => $this->baseUrl,
            'test_email'      => $this->testEmail,
            'test_password'   => $this->testPassword,
            'timeout_seconds' => $this->timeoutSeconds,
            'pw_workers'      => $this->pwWorkers,
            'pw_retries'      => $this->pwRetries,
        ], [
            'base_url'        => ['required', 'url'],
            'test_email'      => ['required', 'email'],
            'test_password'   => ['required', 'min:6'],
            'timeout_seconds' => ['required', 'integer', 'min:5', 'max:120'],
            'pw_workers'      => ['required', 'integer', 'min:1', 'max:16'],
            'pw_retries'      => ['required', 'integer', 'min:0', 'max:5'],
        ]);

        if ($validator->fails()) {
            Notification::make()
                ->title('Please fix the configuration errors')
                ->body(implode(' ', $validator->errors()->all()))
                ->danger()
                ->send();
            return false;
        }

        return true;
    }

    // ── Download ─────────────────────────────────────────────────────────

    public function downloadZip(): BinaryFileResponse|StreamedResponse
    {
        if (! $this->validateScenarioSelection() || ! $this->validateConfiguration()) {
            // Redirect back to the failing step
            $this->currentStep = empty($this->selectedScenarios) ? 2 : 3;
            return response()->stream(fn () => null, 200);
        }

        $config = [
            'framework'       => $this->framework,
            'platform'        => $this->ecommercePlatform,
            'scenarios'       => $this->selectedScenarios,
            'base_url'        => $this->baseUrl,
            'admin_url'       => $this->adminUrl ?: null,
            'test_email'      => $this->testEmail,
            'test_password'   => $this->testPassword,
            'timeout_seconds' => $this->timeoutSeconds,
            'headless'        => $this->headless,
            'pw_workers'      => $this->pwWorkers,
            'pw_retries'      => $this->pwRetries,
        ];

        try {
            $zipPath  = app(TestGeneratorService::class)->generate($config);
            $filename = $this->framework . '-tests-' . now()->format('Y-m-d') . '.zip';

            return response()->download($zipPath, $filename, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Test generator ZIP failed', ['exception' => $e]);

            Notification::make()
                ->title('ZIP generation failed')
                ->body('An error occurred generating the test suite. Please try again or contact support.')
                ->danger()
                ->persistent()
                ->send();

            return response()->stream(fn () => null, 200);
        }
    }

    // ── Helpers for the view ──────────────────────────────────────────────

    public function getScenarioOptions(): array
    {
        return [
            'homepage'             => ['label' => 'Homepage',              'description' => 'Page loads, navigation, search input, hero banner'],
            'category'             => ['label' => 'Category / PLP',        'description' => 'Product listing, sort, filters, click-through to PDP'],
            'product'              => ['label' => 'Product Detail (PDP)',  'description' => 'Title, price, gallery, quantity input'],
            'add_to_cart'          => ['label' => 'Add to Cart',           'description' => 'Button visibility, success message, cart count update'],
            'cart'                 => ['label' => 'Cart',                  'description' => 'Cart items, qty update, remove item, checkout button'],
            'guest_checkout'       => ['label' => 'Guest Checkout',        'description' => 'Shipping form, address entry (order placement commented out by default)'],
            'account_registration' => ['label' => 'Account Registration',  'description' => 'Register form, validation, successful account creation'],
            'auth'                 => ['label' => 'Login & Logout',        'description' => 'Valid/invalid credentials, session creation, logout'],
            'search'               => ['label' => 'Search',                'description' => 'Search input, results display, click-through to product'],
        ];
    }

    public function getPlatformLabel(): string
    {
        return match ($this->ecommercePlatform) {
            'magento-luma' => 'Magento 2 (Luma)',
            'magento-hyva' => 'Magento 2 (Hyva)',
            default        => 'Generic',
        };
    }

    public function getFrameworkLabel(): string
    {
        return match ($this->framework) {
            'cypress'    => 'Cypress',
            'playwright' => 'Playwright',
            default      => ucfirst($this->framework),
        };
    }

    public function selectAllScenarios(): void
    {
        $this->selectedScenarios = array_keys($this->getScenarioOptions());
    }

    public function clearAllScenarios(): void
    {
        $this->selectedScenarios = [];
    }

    public function toggleScenario(string $key): void
    {
        if (in_array($key, $this->selectedScenarios)) {
            $this->selectedScenarios = array_values(array_filter(
                $this->selectedScenarios,
                fn ($s) => $s !== $key
            ));
        } else {
            $this->selectedScenarios[] = $key;
        }
    }
}
