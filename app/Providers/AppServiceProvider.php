<?php

namespace App\Providers;

use Filament\Notifications\Livewire\DatabaseNotifications;
use Filament\Pages\Page;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentColor;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

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
        // // Register the schedule if we're running in the console
        // if ($this->app->runningInConsole()) {
        //     $this->app->booted(function () {
        //         $schedule = $this->app->make(Schedule::class);
                
        //         // Run prediction analysis daily at 2 AM
        //         $schedule->command('app:run-prediction-analysis')->dailyAt('02:00')
        //             ->appendOutputTo(storage_path('logs/prediction-analysis.log'));
                
        //         // You could also run it more frequently for testing
        //         // $schedule->command('app:run-prediction-analysis --time-window=6')->everyFourHours();
        //     });
        // }

        FilamentColor::register([
            'danger' => Color::Red,
            'gray' => Color::Zinc,
            'info' => Color::Blue,
            'primary' => Color::hex('#111111'),
            'success' => Color::Green,
            'warning' => Color::Amber,
            'yellow' => Color::Yellow,
        ]);


        FilamentIcon::register([
            'panels::sidebar.expand-button' => 'heroicon-o-bars-3',
        ]);

        Page::formActionsAlignment(Alignment::Right);

        DatabaseNotifications::trigger('filament.notifications.database-notifications-trigger');
    }
}
