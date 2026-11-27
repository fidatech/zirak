<?php

namespace App\Filament\Teach\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getTitle(): string
    {
        return __('teach.nav.dashboard');
    }

    public static function getNavigationLabel(): string
    {
        return __('teach.nav.dashboard');
    }
}
