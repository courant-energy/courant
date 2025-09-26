<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AlertsTableWidget;
use App\Filament\Widgets\CustomAccountWidget;
use App\Filament\Widgets\DeviceStatsWidget;
use App\Filament\Widgets\EnergyCostCalculator;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Widgets\AccountWidget;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    // protected static ?string $navigationGroup = 'Main Navigation';

    protected static string $view = 'filament.pages.dashboard';

    public $defaultAction = 'onboarding';

    public function onboardingAction(): Action
    {
        return Action::make('onboarding')
            ->modalHeading('On boarding')
            ->modalContent(view('onboarding'))
            ->modalSubmitActionLabel('Okay, got it!')
            ->modalCancelAction(false)
            ->action(function () {
                auth()->user()->update(['is_onboarded' => true]);
                Notification::make()
                    ->title('Onboarding completed')
                    ->success()
                    ->body('You have successfully completed the onboarding process.')
                    ->send();
            })
            ->visible(fn(): bool => ! auth()->user()->is_onboarded);
    }

    protected function getFooterWidgets(): array
    {
        return [
            CustomAccountWidget::class,
            DeviceStatsWidget::class,
            EnergyCostCalculator::class,
            AlertsTableWidget::class,
        ];
    }
}
