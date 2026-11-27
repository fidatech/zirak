<?php

namespace App\Providers;

use App\Services\Settings;
use App\Services\Sms\LogSmsSender;
use App\Services\Sms\SmsSender;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);

        $this->app->bind(SmsSender::class, fn () => match (config('zirak.sms_driver')) {
            default => new LogSmsSender,
        });
    }

    public function boot(): void
    {
        // Dari and Pashto fall back to Persian ("fa") for package strings (Filament ships "fa" but not "prs"/"ps"), then English.
        $this->app['translator']->determineLocalesUsing(function (array $locales) {
            if (in_array($locales[0] ?? null, ['prs', 'ps'], true)) {
                array_splice($locales, 1, 0, ['fa']);
            }

            return $locales;
        });

        // Keys saved in Admin → Settings override .env.
        $this->app->make(Settings::class)->apply();
    }
}
