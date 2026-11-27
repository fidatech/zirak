<?php

namespace App\Filament\Widgets;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\TeacherApplication;
use App\Models\TutorMessage;
use Filament\Widgets\Widget;

/** "Welcome back" banner with the things that need the admin's attention. */
class AdminWelcome extends Widget
{
    protected string $view = 'filament.widgets.admin-welcome';

    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        return [
            'name' => auth()->user()->name ?: 'Admin',
            'items' => [
                ['label' => 'Courses to review', 'count' => Course::where('status', 'review')->count(), 'url' => '/admin/courses?tableFilters[status][value]=review', 'icon' => '📝'],
                ['label' => 'Teacher applications', 'count' => TeacherApplication::where('status', 'pending')->count(), 'url' => '/admin/teacher-applications', 'icon' => '🧑‍🏫'],
                ['label' => 'Student questions', 'count' => TutorMessage::waitingForTeacher()->count(), 'url' => '/admin/student-questions', 'icon' => '💬'],
                ['label' => 'Videos that failed', 'count' => Lesson::where('video_status', 'failed')->count(), 'url' => '/admin/courses', 'icon' => '🎬'],
            ],
        ];
    }
}
