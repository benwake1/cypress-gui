<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Filament\Pages;

use App\DTOs\AiGenerationResult;
use App\Enums\ConversationStatus;
use App\Enums\SourceType;
use App\Models\AiConversation;
use App\Models\ManagedTestFile;
use App\Models\Project;
use App\Models\TestSuite;
use App\Models\AppSetting;
use App\Services\AiTestGeneratorService;
use App\Services\SiteCrawlerService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiTestBuilderPage extends Page
{
    protected static ?string $navigationIcon  = null;
    protected static ?string $navigationLabel = 'AI Test Builder';
    protected static ?string $navigationGroup = 'Testing';
    protected static ?int    $navigationSort  = 6;
    protected static string  $view            = 'filament.pages.ai-test-builder';
    protected static ?string $title           = 'AI Test Builder';
    protected static ?string $slug            = 'ai-test-builder';

    public ?int $projectId = null;
    public ?string $conversationUlid = null;
    public ?int $preloadedSuiteId = null;
    public string $userMessage = '';
    public string $crawlUrl = '';
    public string $framework = 'cypress';
    public bool $isCrawling = false;
    public bool $isGenerating = false;
    public array $generatedFiles = [];
    public array $chatMessages = [];
    public string $suiteName = '';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!($user?->isAdmin() || $user?->isPM())) {
            return false;
        }

        return !empty(AppSetting::get('ai_anthropic_api_key'));
    }

    public function mount(): void
    {
        $projectId = request()->integer('project_id') ?: null;
        if ($projectId && Project::where('id', $projectId)->exists()) {
            $this->projectId = $projectId;
            $project = Project::find($projectId);
            $this->framework = $project->runner_type->value ?? 'cypress';
        }

        $conversationUlid = request()->query('conversation');
        if ($conversationUlid) {
            $this->loadConversation($conversationUlid);
            return;
        }

        $suiteId = request()->integer('suite_id') ?: null;
        if ($suiteId) {
            $this->loadSuiteFiles($suiteId);
        }
    }

    public function getConversation(): ?AiConversation
    {
        if (!$this->conversationUlid) {
            return null;
        }

        return AiConversation::where('ulid', $this->conversationUlid)->first();
    }

    public function getConversationsProperty(): array
    {
        if (!$this->projectId) {
            return [];
        }

        return AiConversation::where('project_id', $this->projectId)
            ->where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get()
            ->map(fn (AiConversation $c) => [
                'ulid' => $c->ulid,
                'title' => $c->title ?: 'Untitled conversation',
                'status' => $c->status->value,
                'updated_at' => $c->updated_at->diffForHumans(),
            ])
            ->toArray();
    }

    public function getProjectsProperty(): array
    {
        return Project::orderBy('name')
            ->get(['id', 'name', 'runner_type'])
            ->toArray();
    }

    public function selectProject(int $projectId): void
    {
        $this->projectId = $projectId;
        $this->conversationUlid = null;
        $this->preloadedSuiteId = null;
        $this->chatMessages = [];
        $this->generatedFiles = [];

        $project = Project::find($projectId);
        if ($project) {
            $this->framework = $project->runner_type->value ?? 'cypress';
        }
    }

    public function newConversation(): void
    {
        $this->conversationUlid = null;
        $this->preloadedSuiteId = null;
        $this->chatMessages = [];
        $this->generatedFiles = [];
        $this->userMessage = '';
        $this->crawlUrl = '';
        $this->suiteName = '';
        $this->isCrawling = false;
        $this->isGenerating = false;
    }

    public function loadConversation(string $ulid): void
    {
        $conversation = AiConversation::where('ulid', $ulid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$conversation) {
            Notification::make()->title('Conversation not found')->danger()->send();
            return;
        }

        $this->conversationUlid = $conversation->ulid;
        $this->projectId = $conversation->project_id;
        $this->framework = $conversation->framework ?? $conversation->project?->runner_type?->value ?? 'cypress';
        $this->chatMessages = $conversation->messages ?? [];
        $this->rebuildFilesFromMessages();

        $this->dispatch('scroll-chat');
    }

    public function loadSuiteFiles(int $suiteId): void
    {
        $suite = TestSuite::where('id', $suiteId)
            ->where('source_type', SourceType::Managed)
            ->first();

        if (!$suite) {
            return;
        }

        $this->projectId = $suite->project_id;
        $this->framework = $suite->getEffectiveRunnerType()->value;
        $this->suiteName = $suite->name;
        $this->preloadedSuiteId = $suite->id;

        $this->generatedFiles = [];
        foreach ($suite->managedTestFiles as $file) {
            $this->generatedFiles[$file->file_path] = $file->content;
        }

        if (empty($this->generatedFiles)) {
            $this->chatMessages = [
                [
                    'role' => 'assistant',
                    'content' => "Opened the **{$suite->name}** suite. It has no test files yet — describe what you'd like to test and I'll generate them.",
                    'timestamp' => now()->toIso8601String(),
                ],
            ];
        } else {
            $this->chatMessages = [
                [
                    'role' => 'assistant',
                    'content' => "Loaded " . count($this->generatedFiles) . " existing test file(s) from the **{$suite->name}** suite. You can ask me to modify, extend, or regenerate these tests.",
                    'timestamp' => now()->toIso8601String(),
                ],
            ];
        }
    }

    public function sendMessage(): void
    {
        $message = trim($this->userMessage);
        if ($message === '') {
            return;
        }

        if (!$this->projectId) {
            Notification::make()->title('Please select a project first')->warning()->send();
            return;
        }

        $this->isGenerating = true;
        $this->userMessage = '';

        try {
            $generator = app(AiTestGeneratorService::class);

            $conversation = $this->getConversation();
            $isNew = !$conversation;

            if ($isNew) {
                $project = Project::find($this->projectId);
                $conversation = AiConversation::create([
                    'user_id' => auth()->id(),
                    'project_id' => $this->projectId,
                    'test_suite_id' => $this->preloadedSuiteId,
                    'title' => Str::limit($message, 80),
                    'messages' => [],
                    'crawl_data' => $project?->crawl_data,
                    'framework' => $this->framework,
                    'status' => ConversationStatus::Active,
                ]);
                $this->conversationUlid = $conversation->ulid;
                $this->preloadedSuiteId = null;
            }

            // If pre-seeded from a suite, include existing files as context
            if ($isNew && !empty($this->generatedFiles)) {
                $fileContext = "Here are the existing test files:\n\n";
                foreach ($this->generatedFiles as $path => $content) {
                    $fileContext .= "```javascript file:{$path}\n{$content}\n```\n\n";
                }
                $message = $fileContext . $message;
            }

            $result = $isNew
                ? $generator->generate($conversation, $message, $this->framework)
                : $generator->refine($conversation, $message, $this->framework);

            $this->chatMessages = $conversation->fresh()->messages ?? [];
            $this->mergeGeneratedFiles($result);

            $this->dispatch('scroll-chat');

            Notification::make()->title('Response generated')->success()->send();

        } catch (\InvalidArgumentException $e) {
            Notification::make()->title($e->getMessage())->warning()->send();
        } catch (\Throwable $e) {
            Log::error('AI generation failed', ['error' => $e->getMessage()]);
            Notification::make()
                ->title('Generation failed')
                ->body('An error occurred. Please try again.')
                ->danger()
                ->send();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function crawlSite(): void
    {
        $url = trim($this->crawlUrl);
        if ($url === '') {
            Notification::make()->title('Please enter a URL to crawl')->warning()->send();
            return;
        }

        if (!$this->projectId) {
            Notification::make()->title('Please select a project first')->warning()->send();
            return;
        }

        $this->isCrawling = true;

        try {
            $crawler = app(SiteCrawlerService::class);
            $result = $crawler->crawl($url);
            $crawlArray = $result->toArray();

            $project = Project::find($this->projectId);
            $project?->update(['crawl_data' => $crawlArray]);

            $conversation = $this->getConversation();
            if (!$conversation) {
                $conversation = AiConversation::create([
                    'user_id' => auth()->id(),
                    'project_id' => $this->projectId,
                    'title' => "Crawl: {$url}",
                    'messages' => [],
                    'crawl_data' => $crawlArray,
                    'framework' => $this->framework,
                    'status' => ConversationStatus::Active,
                ]);
                $this->conversationUlid = $conversation->ulid;
            } else {
                $conversation->update(['crawl_data' => $crawlArray]);
            }

            $this->crawlUrl = '';
            Notification::make()
                ->title('Site crawled successfully')
                ->body("Found {$result->interactiveElementCount()} interactive elements and {$result->formCount()} forms.")
                ->success()
                ->send();

        } catch (\InvalidArgumentException $e) {
            Notification::make()->title($e->getMessage())->warning()->send();
        } catch (\Throwable $e) {
            Log::error('Site crawl failed', ['url' => $url, 'error' => $e->getMessage()]);
            Notification::make()
                ->title('Crawl failed')
                ->body($e->getMessage())
                ->danger()
                ->send();
        } finally {
            $this->isCrawling = false;
        }
    }

    public function switchFramework(string $framework): void
    {
        if (!in_array($framework, ['cypress', 'playwright'])) {
            return;
        }

        $previousFramework = $this->framework;
        $this->framework = $framework;

        $conversation = $this->getConversation();

        // Persist the framework choice on the conversation
        if ($conversation) {
            $conversation->update(['framework' => $framework]);
        }

        // Only trigger conversion if the framework actually changed and there are files to convert
        if ($previousFramework === $framework || !$conversation || empty($this->generatedFiles)) {
            return;
        }

        $this->isGenerating = true;

        try {
            $generator = app(AiTestGeneratorService::class);
            $result = $generator->regenerateForFramework($conversation, $framework);

            $this->chatMessages = $conversation->fresh()->messages ?? [];
            $this->mergeGeneratedFiles($result);

            $this->dispatch('scroll-chat');

            Notification::make()->title("Tests converted to {$framework}")->success()->send();

        } catch (\Throwable $e) {
            Log::error('Framework switch failed', ['error' => $e->getMessage()]);
            Notification::make()->title('Conversion failed')->danger()->send();
        } finally {
            $this->isGenerating = false;
        }
    }

    public function getLinkedSuiteProperty(): ?TestSuite
    {
        $conversation = $this->getConversation();
        if (!$conversation?->test_suite_id) {
            return null;
        }

        return TestSuite::find($conversation->test_suite_id);
    }

    public function saveAsSuite(): void
    {
        if (empty($this->generatedFiles)) {
            Notification::make()->title('No generated files to save')->warning()->send();
            return;
        }

        $conversation = $this->getConversation();
        $existingSuite = $this->linkedSuite;

        try {
            if ($existingSuite) {
                // Update existing managed suite files
                $existingSuite->update(['runner_type' => $this->framework]);
                $existingSuite->managedTestFiles()->delete();

                foreach ($this->generatedFiles as $path => $content) {
                    ManagedTestFile::create([
                        'test_suite_id' => $existingSuite->id,
                        'file_path' => $path,
                        'content' => $content,
                        'generated_by' => auth()->id(),
                    ]);
                }

                Notification::make()
                    ->title('Suite updated')
                    ->body("Updated \"{$existingSuite->name}\" with " . count($this->generatedFiles) . " files.")
                    ->success()
                    ->send();
            } else {
                // Create new suite
                $name = trim($this->suiteName);
                if ($name === '') {
                    Notification::make()->title('Please enter a suite name')->warning()->send();
                    return;
                }

                $suite = TestSuite::create([
                    'project_id' => $this->projectId,
                    'source_type' => SourceType::Managed,
                    'runner_type' => $this->framework,
                    'name' => $name,
                    'spec_pattern' => '**/*.spec.{js,ts}',
                    'active' => true,
                ]);

                foreach ($this->generatedFiles as $path => $content) {
                    ManagedTestFile::create([
                        'test_suite_id' => $suite->id,
                        'file_path' => $path,
                        'content' => $content,
                        'generated_by' => auth()->id(),
                    ]);
                }

                if ($conversation) {
                    $conversation->update([
                        'test_suite_id' => $suite->id,
                        'status' => ConversationStatus::Completed,
                    ]);
                }

                $this->suiteName = '';

                Notification::make()
                    ->title('Test suite saved')
                    ->body("Created managed suite \"{$name}\" with " . count($this->generatedFiles) . " files.")
                    ->success()
                    ->send();
            }
        } catch (\Throwable $e) {
            Log::error('Failed to save managed suite', ['error' => $e->getMessage()]);
            Notification::make()->title('Failed to save suite')->danger()->send();
        }
    }

    public function downloadZip()
    {
        if (empty($this->generatedFiles)) {
            Notification::make()->title('No files to download')->warning()->send();
            return;
        }

        $zipPath = tempnam(sys_get_temp_dir(), 'ai-tests-') . '.zip';
        $zip = new \ZipArchive();

        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            Notification::make()->title('Failed to create ZIP file')->danger()->send();
            return;
        }

        foreach ($this->generatedFiles as $path => $content) {
            $zip->addFromString($path, $content);
        }

        $zip->close();

        $filename = $this->framework . '-ai-tests-' . now()->format('Y-m-d') . '.zip';

        return response()->download($zipPath, $filename, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    public function clearGeneratedFiles(): void
    {
        $this->generatedFiles = [];
    }

    public function getProjectCrawlUrl(): ?string
    {
        if (!$this->projectId) {
            return null;
        }

        $project = Project::find($this->projectId);
        return $project?->crawl_data['url'] ?? null;
    }

    private function mergeGeneratedFiles(AiGenerationResult $result): void
    {
        foreach ($result->files as $path => $content) {
            $this->generatedFiles[$path] = $content;
        }
    }

    private function rebuildFilesFromMessages(): void
    {
        $this->generatedFiles = [];

        foreach ($this->chatMessages as $msg) {
            if (($msg['role'] ?? '') !== 'assistant') {
                continue;
            }

            $content = $msg['content'] ?? '';
            preg_match_all('/```(?:javascript|js|typescript|ts)\s+file:(.+?)\n(.*?)```/s', $content, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                $this->generatedFiles[trim($match[1])] = trim($match[2]);
            }
        }
    }
}
