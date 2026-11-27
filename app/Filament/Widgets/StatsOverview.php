<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use App\Support\DailySeries;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    private function trend(Stat $stat, ?int $change, string $suffix): Stat
    {
        if ($change === null) {
            return $stat->description($suffix);
        }

        return $stat->description(($change >= 0 ? '+' : '').$change.'% vs last week')
            ->descriptionIcon($change >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
            ->color($change >= 0 ? 'success' : 'danger');
    }

    protected function getStats(): array
    {
        $students = User::where('role', 'student');
        $paid = Order::where('status', OrderStatus::Paid)->where('currency', 'AFN');
        $certs = Certificate::where('status', 'valid');

        return [
            $this->trend(Stat::make('Students', number_format((clone $students)->count()))->icon('heroicon-o-users')
                ->chart(array_values(DailySeries::make($students, 'created_at', 14))),
                DailySeries::change($students, 'created_at'), number_format((clone $students)->where('created_at', '>=', now()->subDays(7))->count()).' new this week'),
            $this->trend(Stat::make('Enrollments', number_format(Enrollment::count()))->icon('heroicon-o-academic-cap')
                ->chart(array_values(DailySeries::make(Enrollment::query(), 'created_at', 14))),
                DailySeries::change(Enrollment::query(), 'created_at'), 'All courses'),
            $this->trend(Stat::make('Revenue (AFN, 30 days)', number_format((clone $paid)->where('paid_at', '>=', now()->subDays(30))->sum('total')))->icon('heroicon-o-banknotes')
                ->chart(array_values(DailySeries::make($paid, 'paid_at', 14, 'sum', 'total'))),
                DailySeries::change($paid, 'paid_at', 7, 'sum', 'total'), number_format((clone $paid)->sum('total')).' AFN in total'),
            $this->trend(Stat::make('Certificates issued', number_format((clone $certs)->count()))->icon('heroicon-o-trophy')
                ->chart(array_values(DailySeries::make($certs, 'issued_at', 14))),
                DailySeries::change($certs, 'issued_at'), 'Valid certificates'),
        ];
    }
}
