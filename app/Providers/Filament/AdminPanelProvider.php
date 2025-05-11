<?php

namespace App\Providers\Filament;

use App\Http\Middleware\AdminOnly;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\\Filament\\Admin\\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\\Filament\\Admin\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\\Filament\\Admin\\Widgets')
            ->widgets([
                \App\Filament\Admin\Widgets\AdminStatsWidget::class,
                \App\Filament\Admin\Widgets\LatestQuestionsWidget::class,
            ])
            ->plugin(\TomatoPHP\FilamentPWA\FilamentPWAPlugin::make())
            ->brandLogo(asset('logo.svg'))
            ->darkModeBrandLogo(asset('logo-dark.svg'))
            ->sidebarCollapsibleOnDesktop()
            ->favicon(asset('favicon.svg'))
            ->darkMode()
            ->renderHook(PanelsRenderHook::BODY_END, fn() => Blade::render("@vite('resources/js/app.js')"))
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                AdminOnly::class,
            ])
            ->navigationGroups([
                'Device Management',
                'Help & Learn',
                'User Management',
                'Settings',
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
