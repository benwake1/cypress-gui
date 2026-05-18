<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Filament\Pages;

use App\Filament\Pages\Concerns\HandlesSecretFields;
use App\Models\AppSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;

class AiSettingsPage extends Page
{
    use HandlesSecretFields;

    protected static ?string $navigationIcon  = null;
    protected static ?string $navigationLabel = 'Anthrophic API';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int    $navigationSort  = 5;
    protected static string  $view            = 'filament.pages.ai-settings';
    protected static ?string $title           = 'Anthropic API Settings';
    protected static ?string $slug            = 'settings/ai';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'ai_anthropic_api_key' => $this->maskSecret('ai_anthropic_api_key'),
            'ai_model'             => AppSetting::get('ai_model', config('ai.model')),
            'ai_max_tokens'        => (int) AppSetting::get('ai_max_tokens', 4096),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Anthropic API')
                    ->description('Configure your Anthropic API key for the AI Test Builder. Get your key from console.anthropic.com.')
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        Forms\Components\TextInput::make('ai_anthropic_api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('sk-ant-...')
                            ->helperText(fn (Forms\Get $get) => $get('ai_anthropic_api_key') === self::SECRET_PLACEHOLDER
                                ? 'An API key is saved. Clear the field and type a new one to change it.'
                                : 'Your API key is encrypted at rest and never leaves this server.'),

                        Forms\Components\Select::make('ai_model')
                            ->label('Model')
                            ->options([
                                'claude-sonnet-4-6'  => 'Claude Sonnet 4.6 (recommended)',
                                'claude-haiku-4-5-20251001' => 'Claude Haiku 4.5 (faster, lower cost)',
                            ])
                            ->default('claude-sonnet-4-6'),

                        Forms\Components\TextInput::make('ai_max_tokens')
                            ->label('Max Response Length')
                            ->numeric()
                            ->minValue(1024)
                            ->maxValue(16384)
                            ->default(4096)
                            ->suffix('tokens')
                            ->helperText('The maximum length of each AI response. One token ≈ 4 characters of code. '
                                . 'The default (4,096 tokens) is enough for most single-file tests. '
                                . 'Increase to 8,192+ if the AI is cutting off mid-file when generating multiple test files. '
                                . 'Higher values use more of your API quota per response.'),
                    ]),

                Forms\Components\Section::make('Terms & Responsibility')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Forms\Components\Placeholder::make('tos_notice')
                            ->label('')
                            ->content(new HtmlString(
                                '<div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">'
                                . '<p><strong>Your key, your account.</strong> The AI Test Builder connects directly to Anthropic using your API key. '
                                . 'All API usage and costs are billed to your Anthropic account — ' . config('brand.name', config('app.name')) . ' does not process, store, or resell API credits.</p>'
                                . '<p><strong>Security.</strong> Your API key is encrypted at rest using AES-256-CBC and is only decrypted in-memory at the moment of each API call. '
                                . 'It is never exposed in logs, API responses, or browser sessions.</p>'
                                . '<p><strong>Your responsibility.</strong> You are solely responsible for your Anthropic account, API key security, and any charges incurred. '
                                . 'Usage is governed by <a href="https://www.anthropic.com/policies/terms" target="_blank" rel="noopener" class="underline text-primary-600 dark:text-primary-400">Anthropic\'s Terms of Service</a>. '
                                . 'If you suspect your key has been compromised, revoke it immediately at <a href="https://console.anthropic.com" target="_blank" rel="noopener" class="underline text-primary-600 dark:text-primary-400">console.anthropic.com</a>.</p>'
                                . '</div>'
                            )),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->saveSecretIfChanged('ai_anthropic_api_key', $data['ai_anthropic_api_key'] ?? '');
        AppSetting::set('ai_model', $data['ai_model'] ?? 'claude-sonnet-4-6');
        AppSetting::set('ai_max_tokens', (int) ($data['ai_max_tokens'] ?? 4096));

        Notification::make()
            ->title('AI settings saved')
            ->success()
            ->send();
    }
}
