<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class CustomAccountWidget extends Widget
{
    protected array|string|int $columnSpan = 'full';

    protected static string $view = 'filament.widgets.custom-account-widget';
}
