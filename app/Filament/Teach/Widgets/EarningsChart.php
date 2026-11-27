<?php

namespace App\Filament\Teach\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Support\DailySeries;
use Carbon\CarbonImmutable;
use Filament\Widgets\ChartWidget;

class EarningsChart extends ChartWidget
{
    protected static ?int $sort = 1;

    protected ?string $maxHeight = '240px';

    public function getHeading(): ?string
    {
        return __('teach.dash.earnings_chart');
    }

    protected function getData(): array
    {
        $series = DailySeries::make(Order::where('teacher_id', auth()->id())->where('status', OrderStatus::Paid)->where('currency', 'AFN'), 'paid_at', 30, 'sum', 'teacher_amount');

        return [
            'datasets' => [[
                'label' => __('teach.dash.earnings_label'), 'data' => array_values($series),
                'backgroundColor' => 'rgba(91,76,230,.85)', 'borderRadius' => 6, 'maxBarThickness' => 18,
            ]],
            'labels' => array_map(fn ($d) => n(CarbonImmutable::parse($d)->translatedFormat('j M')), array_keys($series)),
        ];
    }

    protected function getOptions(): array
    {
        return ['plugins' => ['legend' => ['display' => false]], 'scales' => ['y' => ['beginAtZero' => true, 'grid' => ['color' => '#f1f5f9']], 'x' => ['grid' => ['display' => false]]]];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
