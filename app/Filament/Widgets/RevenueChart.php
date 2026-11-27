<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Enrollment;
use App\Models\Order;
use App\Support\DailySeries;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Revenue and enrollments — last 30 days';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $revenue = DailySeries::make(Order::where('status', OrderStatus::Paid)->where('currency', 'AFN'), 'paid_at', 30, 'sum', 'total');
        $enrollments = DailySeries::make(Enrollment::query(), 'created_at', 30);

        return [
            'datasets' => [
                ['label' => 'Revenue (AFN)', 'data' => array_values($revenue), 'borderColor' => '#5B4CE6', 'backgroundColor' => 'rgba(91,76,230,.12)', 'fill' => true, 'tension' => .35, 'yAxisID' => 'y'],
                ['label' => 'Enrollments', 'data' => array_values($enrollments), 'borderColor' => '#22C3E6', 'backgroundColor' => 'transparent', 'tension' => .35, 'yAxisID' => 'y1'],
            ],
            'labels' => array_map(fn ($d) => CarbonImmutable::parse($d)->format('j M'), array_keys($revenue)),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'scales' => [
                'y' => ['beginAtZero' => true, 'grid' => ['color' => '#f1f5f9']],
                'y1' => ['beginAtZero' => true, 'position' => 'right', 'grid' => ['display' => false]],
                'x' => ['grid' => ['display' => false]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
