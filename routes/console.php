<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// cPanel has no long-running workers: process queued jobs from cron every minute.
Schedule::command('queue:work --stop-when-empty --max-time=50')->everyMinute()->withoutOverlapping();

// Daily Telegram study reminder, mid-afternoon Kabul time.
Schedule::command('zirak:telegram-reminders')->dailyAt('15:30')->timezone('Asia/Kabul')->withoutOverlapping();

// Lesson videos still uploading/processing on Bunny (backup for missed webhooks).
Schedule::command('zirak:video-sync')->everyFiveMinutes()->withoutOverlapping();

// Share last month's Zirak Plus money with teachers.
Schedule::command('zirak:plan-pool')->monthlyOn(1, '02:00')->timezone('Asia/Kabul')->withoutOverlapping();
