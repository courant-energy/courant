<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\AlertsTableWidget;
use App\Filament\Widgets\DeviceStatsWidget;
use App\Filament\Widgets\EnergyCostCalculator;
use Filament\Pages\Page;
use Filament\Widgets\AccountWidget;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    // protected static ?string $navigationGroup = 'Main Navigation';

    protected static string $view = 'filament.pages.dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            DeviceStatsWidget::class,
            AlertsTableWidget::class,
            AccountWidget::class,
            EnergyCostCalculator::class,
        ];
    }
    
   
}
