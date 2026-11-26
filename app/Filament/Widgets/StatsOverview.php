<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $paid = Order::where('status', OrderStatus::Paid)->where('currency', 'AFN');

        return [
            Stat::make('Students', number_format(User::where('role', 'student')->count()))
                ->description(number_format(User::where('created_at', '>=', now()->subDays(7))->count()).' new this week'),
            Stat::make('Enrollments', number_format(Enrollment::count())),
            Stat::make('Revenue (AFN, paid)', number_format((clone $paid)->sum('total')))
                ->description(number_format((clone $paid)->where('paid_at', '>=', now()->subDays(30))->sum('total')).' in the last 30 days'),
            Stat::make('Certificates issued', number_format(Certificate::where('status', 'valid')->count())),
        ];
    }
}
