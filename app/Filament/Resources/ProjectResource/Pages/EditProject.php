<?php

/**
 * Copyright (c) 2026 Ben Wake
 *
 * This source code is licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_key')
                ->label('Generate Deploy Key')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->requiresConfirmation()
                ->modalDescription('This will generate a new SSH deploy key pair. The existing key (if any) will be replaced.')
                ->action(function () {
                    $keys = $this->record->generateDeployKey();

                    Notification::make()
                        ->title('Deploy key generated!')
                        ->body('Public key: ' . $keys['public'])
                        ->success()
                        ->persistent()
                        ->send();

                    $this->refreshFormData(['deploy_key_public']);
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
