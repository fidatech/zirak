<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/** Teacher area at /teach. Teachers use the normal site login (phone or Telegram), so this panel has no login page. */
class TeachPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('teach')
            ->path('teach')
            ->brandName('Zirak — Teacher area')
            ->favicon('/icons/icon-192.png')
            ->colors(['primary' => Color::Teal])
            ->discoverResources(in: app_path('Filament/Teach/Resources'), for: 'App\Filament\Teach\Resources')
            ->discoverPages(in: app_path('Filament/Teach/Pages'), for: 'App\Filament\Teach\Pages')
            ->discoverWidgets(in: app_path('Filament/Teach/Widgets'), for: 'App\Filament\Teach\Widgets')
            ->navigationItems([
                NavigationItem::make('Back to Zirak')->url('/')->icon('heroicon-o-arrow-uturn-left')->sort(99),
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
            ]);
    }
}
