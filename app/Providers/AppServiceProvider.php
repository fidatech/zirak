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
        // Keys saved in Admin → Settings override .env.
        $this->app->make(Settings::class)->apply();
    }
}
