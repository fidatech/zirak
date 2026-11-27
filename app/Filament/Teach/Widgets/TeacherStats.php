<?php

namespace App\Filament\Teach\Widgets;

use App\Enums\CourseStatus;
use App\Models\Enrollment;
use App\Services\TeacherEarnings;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeacherStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $teacher = auth()->user();
        $earnings = app(TeacherEarnings::class);
        $summary = $earnings->summary($teacher);
        $month = $earnings->thisMonth($teacher);
        $fmt = fn ($rows, $key) => $rows->map(fn ($r, $cur) => TeacherEarnings::money((int) (is_array($r) ? $r[$key] : $r->{$key}), $cur))->implode(' · ') ?: TeacherEarnings::money(0, 'AFN');

        $courses = $teacher->taughtCourses();
        $students = Enrollment::whereIn('course_id', $teacher->taughtCourses()->select('id'))->distinct('user_id')->count('user_id');

        return [
            Stat::make(__('teach.stats.balance'), $fmt($summary, 'balance'))
                ->description(__('teach.stats.balance_desc', ['earned' => $fmt($summary, 'earned'), 'paid' => $fmt($summary, 'paid_out')])),
            Stat::make(__('teach.stats.month'), $fmt($month, 'total'))
                ->description(__('teach.stats.month_desc', ['n' => n($month->sum('sales'))])),
            Stat::make(__('teach.stats.students'), n(number_format($students)))
                ->description(__('teach.stats.students_desc', [
                    'published' => n((clone $courses)->where('status', CourseStatus::Published)->count()),
                    'review' => n((clone $courses)->where('status', CourseStatus::Review)->count()),
                ])),
        ];
    }
}
