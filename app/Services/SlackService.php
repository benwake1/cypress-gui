<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SlackService
{
    private const API_BASE = 'https://slack.com/api';

    public function isEnabled(): bool
    {
        return AppSetting::get('slack_notifications_enabled', '0') === '1';
    }

    public function getBotToken(): string
    {
        return $this->decryptSetting('slack_bot_token');
    }

    /**
     * Look up a Slack user ID by their email address.
     * Returns null if not found or on any API error.
     */
    public function getUserIdByEmail(string $email): ?string
    {
        $token = $this->getBotToken();
        if (! $token) {
            return null;
        }

        try {
            $response = Http::withToken($token)
                ->get(self::API_BASE . '/users.lookupByEmail', ['email' => $email]);

            $body = $response->json();

            if (($body['ok'] ?? false) && isset($body['user']['id'])) {
                return $body['user']['id'];
            }

            // Log non-fatal errors (e.g. user not in workspace) at debug level
            Log::debug('Slack users.lookupByEmail: ' . ($body['error'] ?? 'unknown error'), ['email' => $email]);
        } catch (\Throwable $e) {
            Log::warning('Slack users.lookupByEmail exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Send a Block Kit DM to a Slack user.
     */
    public function sendDm(string $slackUserId, array $blocks): bool
    {
        $token = $this->getBotToken();
        if (! $token) {
            return false;
        }

        try {
            $response = Http::withToken($token)
                ->post(self::API_BASE . '/chat.postMessage', [
                    'channel' => $slackUserId,
                    'blocks'  => $blocks,
                ]);

            $body = $response->json();

            if (! ($body['ok'] ?? false)) {
                Log::warning('Slack chat.postMessage failed: ' . ($body['error'] ?? 'unknown error'), [
                    'slack_user_id' => $slackUserId,
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Slack chat.postMessage exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify the bot token is valid. Used by the settings page test button.
     * Returns ['ok' => bool, 'message' => string].
     */
    public function testConnection(): array
    {
        $token = $this->getBotToken();
        if (! $token) {
            return ['ok' => false, 'message' => 'No bot token configured.'];
        }

        try {
            $response = Http::withToken($token)
                ->post(self::API_BASE . '/auth.test');

            $body = $response->json();

            if ($body['ok'] ?? false) {
                $botName = $body['bot_id'] ?? $body['user'] ?? 'Bot';
                $team    = $body['team'] ?? '';
                return ['ok' => true, 'message' => "Connected as {$botName}" . ($team ? " on {$team}" : '') . '.'];
            }

            return ['ok' => false, 'message' => 'Slack API error: ' . ($body['error'] ?? 'unknown')];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Connection failed: ' . $e->getMessage()];
        }
    }

    /**
     * Decrypt a stored setting, gracefully handling unencrypted legacy values.
     */
    private function decryptSetting(string $key): string
    {
        $stored = AppSetting::get($key, '');

        if (! $stored) {
            return '';
        }

        try {
            return Crypt::decryptString($stored);
        } catch (\Exception) {
            return $stored;
        }
    }
}
