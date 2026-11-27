<?php

namespace App\Filament\Teach\Resources\Courses\Pages;

use App\Enums\CourseStatus;
use App\Filament\Teach\Resources\Courses\CourseResource;
use App\Models\Course;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    public function getSubheading(): ?string
    {
        /** @var Course $course */
        $course = $this->record;
        $status = CourseResource::statusLabel($course->status);

        return match (true) {
            $course->status === CourseStatus::Draft && filled($course->review_note) => __('teach.review.changes', ['status' => $status, 'note' => $course->review_note]),
            $course->status === CourseStatus::Review => __('teach.review.waiting', ['status' => $status]),
            default => __('teach.review.status_line', ['status' => $status]),
        };
    }

    /** What still has to be done before the course can be sent for review. */
    public static function missing(Course $course): array
    {
        $course->loadMissing('chapters.lessons', 'chapters.practiceQuestions', 'chapters.testQuestions');
        $missing = [];
        if ($course->chapters->isEmpty()) {
            $missing[] = __('teach.review.missing_chapter');
        }
        foreach ($course->chapters as $chapter) {
            if ($chapter->lessons->isEmpty()) {
                $missing[] = __('teach.review.missing_lessons', ['chapter' => $chapter->title]);
            }
            if ($chapter->testQuestions->isEmpty()) {
                $missing[] = __('teach.review.missing_test', ['chapter' => $chapter->title]);
            }
        }
        if (blank($course->description)) {
            $missing[] = __('teach.review.missing_description');
        }

        return $missing;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit')->label(__('teach.review.submit'))->icon('heroicon-o-paper-airplane')->color('primary')
                ->visible(fn () => $this->record->status === CourseStatus::Draft)
                ->requiresConfirmation()
                ->modalDescription(__('teach.review.submit_text'))
                ->action(function () {
                    $missing = static::missing($this->record);
                    if ($missing) {
                        Notification::make()->title(__('teach.review.not_ready'))->body(implode("\n", $missing))->warning()->persistent()->send();

                        return;
                    }
                    $this->record->forceFill(['status' => CourseStatus::Review, 'submitted_at' => now(), 'review_note' => null])->save();
                    Notification::make()->title(__('teach.review.sent'))->success()->send();
                    $this->refreshFormData(['status']);
                }),
            Action::make('view')->label(__('teach.review.view_on_site'))->icon('heroicon-o-eye')->color('gray')
                ->url(fn () => route('courses.show', $this->record))->openUrlInNewTab(),
            DeleteAction::make()->visible(fn () => $this->record->status === CourseStatus::Draft && ! $this->record->enrollments()->exists()),
        ];
    }
}
