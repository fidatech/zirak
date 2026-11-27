<?php

namespace App\Filament\Teach\Widgets;

use App\Enums\CourseStatus;
use App\Enums\OrderStatus;
use App\Filament\Teach\Resources\StudentQuestions\StudentQuestionResource;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\TutorMessage;
use App\Services\TeacherEarnings;
use App\Support\DailySeries;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TeacherStats extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected function getStats(): array
    {
        $teacher = auth()->user();
        $earnings = app(TeacherEarnings::class);
        $summary = $earnings->summary($teacher);
        $month = $earnings->thisMonth($teacher);
        $fmt = fn ($rows, $key) => $rows->map(fn ($r, $cur) => TeacherEarnings::money((int) (is_array($r) ? $r[$key] : $r->{$key}), $cur))->implode(sep()) ?: TeacherEarnings::money(0, 'AFN');

        $courses = $teacher->taughtCourses();
        $courseIds = $teacher->taughtCourses()->select('id');
        $students = Enrollment::whereIn('course_id', $courseIds)->distinct('user_id')->count('user_id');
        $sales = Order::where('teacher_id', $teacher->id)->where('status', OrderStatus::Paid);
        $waiting = TutorMessage::waitingForTeacher()->whereHas('course', fn ($q) => $q->where('teacher_id', $teacher->id))->count();

        return [
            Stat::make(__('teach.stats.balance'), $fmt($summary, 'balance'))->icon('heroicon-o-wallet')
                ->description(__('teach.stats.balance_desc', ['earned' => $fmt($summary, 'earned'), 'paid' => $fmt($summary, 'paid_out')])),
            Stat::make(__('teach.stats.month'), $fmt($month, 'total'))->icon('heroicon-o-banknotes')
                ->description(__('teach.stats.month_desc', ['n' => n($month->sum('sales'))]))->color('success')
                ->chart(array_values(DailySeries::make($sales, 'paid_at', 14, 'sum', 'teacher_amount'))),
            Stat::make(__('teach.stats.students'), n(number_format($students)))->icon('heroicon-o-users')
                ->description(__('teach.stats.students_desc', [
                    'published' => n((clone $courses)->where('status', CourseStatus::Published)->count()),
                    'review' => n((clone $courses)->where('status', CourseStatus::Review)->count()),
                ]))->color('primary')
                ->chart(array_values(DailySeries::make(Enrollment::whereIn('course_id', $courseIds), 'created_at', 14))),
            Stat::make(__('teach.stats.questions'), n($waiting))->icon('heroicon-o-chat-bubble-left-right')
                ->description($waiting ? __('teach.stats.questions_desc') : __('teach.stats.questions_none'))
                ->color($waiting ? 'warning' : 'success')
                ->url($waiting ? StudentQuestionResource::getUrl('index') : null),
        ];
    }
}
