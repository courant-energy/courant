<?php

namespace App\Providers;

use Filament\Notifications\Livewire\DatabaseNotifications;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => Color::hex('#111111'),
            'success' => Color::Green,
            'warning' => Color::Amber,
        ]);
        

        FilamentIcon::register([
            'panels::sidebar.expand-button' => 'heroicon-o-bars-3',
        ]);

        Page::formActionsAlignment(Alignment::Right);

        DatabaseNotifications::trigger('filament.notifications.database-notifications-trigger');
    }
}
