<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Enrollment;
use Filament\Widgets\ChartWidget;

class EnrollmentsBySubject extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Enrollments by subject';

    protected ?string $maxHeight = '260px';

    protected function getData(): array
    {
        $counts = Enrollment::query()->join('courses', 'courses.id', '=', 'enrollments.course_id')
            ->selectRaw('courses.category_id, COUNT(*) as total')->groupBy('courses.category_id')->pluck('total', 'category_id');
        $categories = Category::whereIn('id', $counts->keys())->get();
        $colors = ['#6D5DFC', '#22C3E6', '#F472B6', '#F59E0B', '#10B981', '#A78BFA', '#FB7185', '#38BDF8'];

        return [
            'datasets' => [[
                'data' => $categories->map(fn ($c) => (int) $counts[$c->id])->values()->all(),
                'backgroundColor' => array_slice($colors, 0, max(1, $categories->count())),
                'borderWidth' => 0,
            ]],
            'labels' => $categories->map->name_en->values()->all(),
        ];
    }

    protected function getOptions(): array
    {
        return ['cutout' => '68%', 'plugins' => ['legend' => ['position' => 'bottom']], 'scales' => ['x' => ['display' => false], 'y' => ['display' => false]]];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
