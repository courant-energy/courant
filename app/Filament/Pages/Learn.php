<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Learn extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Learning & Support';

    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.pages.learn';
}
