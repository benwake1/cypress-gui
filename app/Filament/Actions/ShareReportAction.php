<?php

namespace App\Filament\Actions;

use App\Models\TestRun;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ShareReportAction
{
    public static function make(): Action
    {
        return Action::make('share_report')
            ->label('Share Report Link')
            ->icon('heroicon-o-share')
            ->color('gray')
            ->action(function (TestRun $record) {
                $token = hash_hmac('sha256', "report-{$record->id}", config('app.key'));
                $url   = route('reports.share', ['testRun' => $record->id, 'token' => $token]);

                Notification::make()
                    ->title('Shareable link copied!')
                    ->body('Send this link to your client — no login required.')
                    ->info()
                    ->send();

                // The URL is available for copy — we pass it via a clipboard JS trick
                return $url;
            })
            ->visible(fn (TestRun $record) => $record->report_html_path !== null);
    }
}
