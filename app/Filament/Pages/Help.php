<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Help extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationGroup = 'Learning & Support';
    

    protected static string $view = 'filament.pages.help';
}
