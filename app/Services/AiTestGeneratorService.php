<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Services;

use App\DTOs\AiGenerationResult;
use App\Models\AiConversation;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class AiTestGeneratorService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';
    private const MAX_TOKENS = 4096;

    private static array $offTopicPatterns = [
        '/write me a (poem|story|essay|song|letter)/i',
        '/translate .+ (to|into) /i',
        '/what is the (capital|population|history) of/i',
        '/tell me (a joke|about yourself)/i',
        '/generate (an? )?(image|picture|logo|design)/i',
        '/help me with (my )?(homework|resume|cover letter)/i',
    ];

    public function generate(AiConversation $conversation, string $userMessage, string $framework = 'cypress'): AiGenerationResult
    {
        $this->validateInput($userMessage);

        $systemPrompt = $this->buildSystemPrompt($framework, $conversation->crawl_data);

        $messages = $conversation->messages ?? [];
        $messages[] = ['role' => 'user', 'content' => $userMessage, 'timestamp' => now()->toIso8601String()];

        $response = $this->callApi($systemPrompt, $messages);

        $messages[] = ['role' => 'assistant', 'content' => $response['content'], 'timestamp' => now()->toIso8601String()];
        $conversation->update(['messages' => $messages]);

        return $this->parseResponse($response);
    }

    public function refine(AiConversation $conversation, string $feedback, string $framework = 'cypress'): AiGenerationResult
    {
        $this->validateInput($feedback);

        $systemPrompt = $this->buildSystemPrompt($framework, $conversation->crawl_data);

        $messages = $conversation->messages ?? [];
        $messages[] = ['role' => 'user', 'content' => $feedback, 'timestamp' => now()->toIso8601String()];

        $response = $this->callApi($systemPrompt, $messages);

        $messages[] = ['role' => 'assistant', 'content' => $response['content'], 'timestamp' => now()->toIso8601String()];
        $conversation->update(['messages' => $messages]);

        return $this->parseResponse($response);
    }

    public function regenerateForFramework(AiConversation $conversation, string $targetFramework): AiGenerationResult
    {
        $systemPrompt = $this->buildSystemPrompt($targetFramework, $conversation->crawl_data);

        $messages = $conversation->messages ?? [];
        $messages[] = [
            'role' => 'user',
            'content' => "Convert the previously generated tests to {$targetFramework} format. Keep the same test scenarios and assertions, but use {$targetFramework} conventions and APIs.",
            'timestamp' => now()->toIso8601String(),
        ];

        $response = $this->callApi($systemPrompt, $messages);

        $messages[] = ['role' => 'assistant', 'content' => $response['content'], 'timestamp' => now()->toIso8601String()];
        $conversation->update(['messages' => $messages]);

        return $this->parseResponse($response);
    }

    public function validateInput(string $message): void
    {
        foreach (self::$offTopicPatterns as $pattern) {
            if (preg_match($pattern, $message)) {
                throw new \InvalidArgumentException(
                    'This request does not appear to be related to test generation. The AI builder only helps with writing, editing, and explaining automated test code.'
                );
            }
        }
    }

    public function streamGenerate(AiConversation $conversation, string $userMessage, string $framework = 'cypress'): \Generator
    {
        $this->validateInput($userMessage);

        $systemPrompt = $this->buildSystemPrompt($framework, $conversation->crawl_data);

        $messages = $conversation->messages ?? [];
        $messages[] = ['role' => 'user', 'content' => $userMessage, 'timestamp' => now()->toIso8601String()];

        $apiKey = $this->getApiKey();
        set_time_limit(120);

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => self::API_VERSION,
            'content-type' => 'application/json',
        ])->timeout(120)->withOptions(['stream' => true])->post(self::API_URL, [
            'model' => AppSetting::get('ai_model', config('ai.model')),
            'max_tokens' => self::MAX_TOKENS,
            'system' => $systemPrompt,
            'messages' => $this->formatMessages($messages),
            'stream' => true,
        ]);

        $fullContent = '';
        $body = $response->getBody();

        while (!$body->eof()) {
            $line = trim($body->read(8192));

            if (empty($line) || !str_starts_with($line, 'data: ')) {
                continue;
            }

            $data = json_decode(substr($line, 6), true);
            if (!$data) continue;

            if ($data['type'] === 'content_block_delta' && isset($data['delta']['text'])) {
                $fullContent .= $data['delta']['text'];
                yield ['type' => 'delta', 'text' => $data['delta']['text']];
            }

            if ($data['type'] === 'message_stop') {
                break;
            }
        }

        $messages[] = ['role' => 'assistant', 'content' => $fullContent, 'timestamp' => now()->toIso8601String()];
        $conversation->update(['messages' => $messages]);

        yield ['type' => 'done', 'content' => $fullContent];
    }

    private function buildSystemPrompt(string $framework, ?array $crawlData): string
    {
        return View::make('prompts.test-generator-system', [
            'framework' => $framework,
            'crawlData' => $crawlData ?? [],
        ])->render();
    }

    private function callApi(string $systemPrompt, array $messages): array
    {
        $apiKey = $this->getApiKey();
        set_time_limit(120);

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => self::API_VERSION,
            'content-type' => 'application/json',
        ])->timeout(120)->post(self::API_URL, [
            'model' => AppSetting::get('ai_model', config('ai.model')),
            'max_tokens' => self::MAX_TOKENS,
            'system' => $systemPrompt,
            'messages' => $this->formatMessages($messages),
        ]);

        if ($response->failed()) {
            Log::error('Anthropic API call failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('AI generation failed: ' . $response->status());
        }

        $data = $response->json();

        $content = collect($data['content'] ?? [])
            ->where('type', 'text')
            ->pluck('text')
            ->implode('');

        return [
            'content' => $content,
            'tokens_used' => [
                'input' => $data['usage']['input_tokens'] ?? 0,
                'output' => $data['usage']['output_tokens'] ?? 0,
                'total' => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0),
            ],
        ];
    }

    private function formatMessages(array $messages): array
    {
        return collect($messages)
            ->map(fn (array $msg) => [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ])
            ->values()
            ->all();
    }

    private function getApiKey(): string
    {
        $stored = AppSetting::get('ai_anthropic_api_key', '');
        if (!$stored) {
            throw new \RuntimeException('Anthropic API key not configured. Go to Settings > AI to add your key.');
        }
        try {
            return Crypt::decryptString($stored);
        } catch (\Exception) {
            return $stored;
        }
    }

    private function parseResponse(array $response): AiGenerationResult
    {
        $content = $response['content'];
        $files = [];
        $suggestions = [];

        preg_match_all('/```(?:javascript|js|typescript|ts)\s+file:(.+?)\n(.*?)```/s', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $files[trim($match[1])] = trim($match[2]);
        }

        $explanation = preg_replace('/```(?:javascript|js|typescript|ts)\s+file:.+?```/s', '', $content);
        $explanation = trim($explanation);

        if (preg_match('/(?:additional tests|suggestions|you could also|consider testing).*?$/is', $explanation, $sugMatch)) {
            $suggestionBlock = $sugMatch[0];
            preg_match_all('/[-•]\s*(.+)/m', $suggestionBlock, $sugLines);
            $suggestions = $sugLines[1] ?? [];
        }

        return new AiGenerationResult(
            files: $files,
            explanation: $explanation,
            suggestions: $suggestions,
            tokensUsed: $response['tokens_used'],
        );
    }
}
