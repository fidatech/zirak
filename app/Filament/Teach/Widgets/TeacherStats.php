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
        $fmt = fn ($rows, $key) => $rows->map(fn ($r, $cur) => TeacherEarnings::money((int) (is_array($r) ? $r[$key] : $r->{$key}), $cur))->implode(' · ') ?: '0 AFN';

        $courses = $teacher->taughtCourses();
        $students = Enrollment::whereIn('course_id', $teacher->taughtCourses()->select('id'))->distinct('user_id')->count('user_id');

        return [
            Stat::make('Balance to be paid to you', $fmt($summary, 'balance'))
                ->description('Earned '.$fmt($summary, 'earned').' · paid out '.$fmt($summary, 'paid_out')),
            Stat::make('Earned this month', $fmt($month, 'total'))
                ->description($month->sum('sales').' sales'),
            Stat::make('Students', number_format($students))
                ->description((clone $courses)->where('status', CourseStatus::Published)->count().' published · '
                    .(clone $courses)->where('status', CourseStatus::Review)->count().' waiting for review'),
        ];
    }
}
