<?php

namespace App\Providers\Filament;

use App\Filament\Support\LocalAvatarProvider;
use App\Http\Middleware\SetLocale;
use Filament\Actions\Action;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Vite;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/** Teacher area at /teach. Teachers use the normal site login (phone or Telegram), so this panel has no login page. */
class TeachPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('teach')
            ->path('teach')
            ->brandName(fn () => __('teach.brand'))
            ->favicon('/icons/icon-192.png')
            // Lesson video uploads (tus → Bunny Stream). Only the panels load this script.
            ->renderHook(PanelsRenderHook::BODY_END, fn () => Blade::render("@vite('resources/js/video-upload.js')"))
            ->defaultAvatarProvider(LocalAvatarProvider::class)
            ->colors(['primary' => Color::hex('#5B4CE6')])
            ->viteTheme('resources/css/filament/zirak/theme.css')
            // Same font as the site, served from public/build (Dari/Pashto script, no Google/Bunny fonts download).
            ->font('Vazirmatn Variable', url: fn () => Vite::asset('resources/css/teach-font.css'), provider: LocalFontProvider::class)
            ->userMenuItems(collect(config('zirak.locales'))->map(fn (array $l, string $code) => Action::make('lang_'.$code)
                ->label($l['label'])->icon('heroicon-o-language')
                ->url(fn () => url()->current().'?lang='.$code)
            )->values()->all())
            ->discoverResources(in: app_path('Filament/Teach/Resources'), for: 'App\Filament\Teach\Resources')
            ->discoverPages(in: app_path('Filament/Teach/Pages'), for: 'App\Filament\Teach\Pages')
            ->discoverWidgets(in: app_path('Filament/Teach/Widgets'), for: 'App\Filament\Teach\Widgets')
            ->navigationItems([
                NavigationItem::make('back')->label(fn () => __('teach.back_to_site'))->url('/')->icon('heroicon-o-arrow-uturn-left')->sort(99),
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
            // The teacher's own language (their profile setting, or ?lang=), also for Livewire updates.
            ->middleware([SetLocale::class], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
