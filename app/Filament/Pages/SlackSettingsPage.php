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
use App\Services\SlackService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SlackSettingsPage extends Page
{
    use HandlesSecretFields;

    protected static ?string $navigationIcon  = null;
    protected static ?string $navigationLabel = 'Slack';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int    $navigationSort  = 4;
    protected static string  $view            = 'filament.pages.slack-settings';
    protected static ?string $title           = 'Slack Settings';
    protected static ?string $slug            = 'settings/slack';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function mount(): void
    {
        $this->form->fill([
            'slack_notifications_enabled' => AppSetting::get('slack_notifications_enabled', '0') === '1',
            'slack_bot_token'             => $this->maskSecret('slack_bot_token'),
            'slack_signing_secret'        => $this->maskSecret('slack_signing_secret'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Slack Configuration')
                    ->description('Configure a Slack App to send DM notifications to users when their test runs complete.')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        Forms\Components\Toggle::make('slack_notifications_enabled')
                            ->label('Enable Slack DM notifications')
                            ->helperText('When enabled, a DM is sent to the user who triggered each test run upon completion.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slack_bot_token')
                            ->label('Bot Token')
                            ->password()
                            ->placeholder('xoxb-…')
                            ->helperText('Bot User OAuth Token from your Slack App → OAuth & Permissions. Starts with xoxb-.')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('slack_signing_secret')
                            ->label('Signing Secret')
                            ->password()
                            ->placeholder('Enter signing secret')
                            ->helperText('Found under Basic Information → Signing Secret. Used to verify future webhook payloads from Slack.')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        AppSetting::set('slack_notifications_enabled', ($data['slack_notifications_enabled'] ?? false) ? '1' : '0');
        $this->saveSecretIfChanged('slack_bot_token', $data['slack_bot_token'] ?? '');
        $this->saveSecretIfChanged('slack_signing_secret', $data['slack_signing_secret'] ?? '');

        Notification::make()
            ->title('Slack settings saved')
            ->success()
            ->send();
    }

    public function testConnection(): void
    {
        $this->save();

        $result = app(SlackService::class)->testConnection();

        if ($result['ok']) {
            Notification::make()
                ->title('Connection successful')
                ->body($result['message'])
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Connection failed')
                ->body($result['message'])
                ->danger()
                ->persistent()
                ->send();
        }
    }
}
