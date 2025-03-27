<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PowerReadings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-bolt';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationGroup = 'Data & Insights';

    protected static string $view = 'filament.pages.power-readings';
}
