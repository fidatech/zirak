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
            $course->status === CourseStatus::Draft && filled($course->review_note) => "Status: {$status}. Changes requested by the Zirak team: {$course->review_note}",
            $course->status === CourseStatus::Review => "Status: {$status}. The Zirak team will check it soon. You can still make changes.",
            default => "Status: {$status}",
        };
    }

    /** What still has to be done before the course can be sent for review. */
    public static function missing(Course $course): array
    {
        $course->loadMissing('chapters.lessons', 'chapters.practiceQuestions', 'chapters.testQuestions');
        $missing = [];
        if ($course->chapters->isEmpty()) {
            $missing[] = 'Add at least one chapter.';
        }
        foreach ($course->chapters as $chapter) {
            if ($chapter->lessons->isEmpty()) {
                $missing[] = "Chapter \"{$chapter->title}\" has no lessons.";
            }
            if ($chapter->testQuestions->isEmpty()) {
                $missing[] = "Chapter \"{$chapter->title}\" has no test questions.";
            }
        }
        if (blank($course->description)) {
            $missing[] = 'Write a course description.';
        }

        return $missing;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('submit')->label('Send for review')->icon('heroicon-o-paper-airplane')->color('primary')
                ->visible(fn () => $this->record->status === CourseStatus::Draft)
                ->requiresConfirmation()
                ->modalDescription('The Zirak team checks the course and publishes it, or sends it back with notes.')
                ->action(function () {
                    $missing = static::missing($this->record);
                    if ($missing) {
                        Notification::make()->title('Not ready yet')->body(implode("\n", $missing))->warning()->persistent()->send();

                        return;
                    }
                    $this->record->forceFill(['status' => CourseStatus::Review, 'submitted_at' => now(), 'review_note' => null])->save();
                    Notification::make()->title('Sent for review')->success()->send();
                    $this->refreshFormData(['status']);
                }),
            Action::make('view')->label('Open on the site')->icon('heroicon-o-eye')->color('gray')
                ->url(fn () => route('courses.show', $this->record))->openUrlInNewTab(),
            DeleteAction::make()->visible(fn () => $this->record->status === CourseStatus::Draft && ! $this->record->enrollments()->exists()),
        ];
    }
}
