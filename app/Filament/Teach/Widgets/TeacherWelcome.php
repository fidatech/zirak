<?php

namespace App\Filament\Teach\Widgets;

use App\Enums\CourseStatus;
use App\Filament\Teach\Resources\Courses\CourseResource;
use App\Filament\Teach\Resources\StudentQuestions\StudentQuestionResource;
use App\Models\TutorMessage;
use Filament\Widgets\Widget;

class TeacherWelcome extends Widget
{
    protected string $view = 'filament.widgets.teacher-welcome';

    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $teacher = auth()->user();

        return [
            'name' => $teacher->publicName(),
            'items' => [
                ['label' => __('teach.dash.new_course'), 'count' => null, 'url' => CourseResource::getUrl('create'), 'icon' => '➕'],
                ['label' => __('teach.dash.questions_waiting'), 'count' => TutorMessage::waitingForTeacher()->whereHas('course', fn ($q) => $q->where('teacher_id', $teacher->id))->count(), 'url' => StudentQuestionResource::getUrl('index'), 'icon' => '💬'],
                ['label' => __('teach.dash.in_review'), 'count' => $teacher->taughtCourses()->where('status', CourseStatus::Review)->count(), 'url' => CourseResource::getUrl('index'), 'icon' => '📝'],
            ],
        ];
    }
}
