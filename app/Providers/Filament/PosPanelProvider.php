<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Pos\Pages\QuickOrder;
use App\Filament\Pos\Pages\ShiftManagement;

class PosPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('pos')
            ->path('pos')
            ->login()
            ->registration()
            ->passwordReset()
            ->colors([
                'primary' => Color::hex('8B4513'), // Deep Coffee
                'secondary' => Color::hex('D4A574'), // Creamy Milk
                'success' => Color::hex('2E7D32'), // Dark Green
                'warning' => Color::hex('F57F17'), // Amber
                'danger' => Color::hex('C62828'), // Dark Red
            ])
            ->discoverResources(in: app_path('Filament/Pos/Resources'), for: 'App\Filament\Pos\Resources')
            ->discoverPages(in: app_path('Filament/Pos/Pages'), for: 'App\Filament\Pos\Pages')
            ->pages([
                QuickOrder::class,
                ShiftManagement::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Pos/Widgets'), for: 'App\Filament\Pos\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->favicon(asset('images/favicon.ico'))
            ->brandName('Warkop POS')
            ->brandLogo(asset('images/logo.svg'))
            ->brandLogoHeight('40px');
    }
}