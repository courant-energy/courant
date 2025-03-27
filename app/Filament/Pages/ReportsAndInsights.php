<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ReportsAndInsights extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationGroup = 'Data & Insights';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.reports-and-insights';
}
