<?php

namespace App\Filament\Admin\Resources\QuestionResource\Pages;

use App\Filament\Admin\Resources\QuestionResource;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\ActionSize;

class ViewQuestion extends ViewRecord
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ActionGroup::make(

                [
                    Actions\Action::make('markAsResolved')
                        ->label('Resolve')
                        ->visible(fn () => $this->record->status === 'pending')
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->action(function () {
                            $this->record->markAsResolved();
                            Notification::make()
                                ->title('Question marked as resolved.')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Actions\Action::make('markAsRejected')
                        ->label('Reject')
                        ->visible(fn () => $this->record->status === 'pending')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->action(function () {
                            $this->record->markAsRejected();
                            Notification::make()
                                ->title('Question marked as rejected.')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Actions\Action::make('reopen')
                        ->label('Reopen')
                        ->visible(fn () => $this->record->status !== 'pending')
                        ->icon('heroicon-o-arrow-left-circle')
                        ->color('warning')
                        ->action(function () {
                            $this->record->markAsPending();
                            Notification::make()
                                ->title('Question reopened.')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                ]
            )
                ->label('Actions')
                ->button()
                ->size(ActionSize::Small),
        ];
    }
}
