<?php

namespace App\Services;

use App\Enums\CourseStatus;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\TeacherApplication;
use App\Models\User;
use App\Services\Telegram\TelegramClient;
use Illuminate\Support\Facades\DB;
use Throwable;

/** Teacher applications and course review. */
class TeacherService
{
    public function __construct(private TelegramClient $telegram) {}

    public function pendingApplication(User $user): ?TeacherApplication
    {
        return $user->teacherApplications()->where('status', 'pending')->latest('id')->first();
    }

    public function apply(User $user, array $data): TeacherApplication
    {
        return $this->pendingApplication($user) ?? $user->teacherApplications()->create($data);
    }

    public function approveApplication(TeacherApplication $application, User $admin): void
    {
        DB::transaction(function () use ($application, $admin) {
            $application->forceFill(['status' => 'approved', 'reviewed_by' => $admin->id, 'reviewed_at' => now()])->save();
            $user = $application->user;
            $user->forceFill([
                'role' => $user->role === UserRole::Admin ? UserRole::Admin : UserRole::Instructor,
                'name' => $user->name ?: $application->full_name,
                'teacher_headline' => $user->teacher_headline ?: $application->headline,
                'teacher_bio' => $user->teacher_bio ?: $application->about,
            ])->save();
        });
        $this->notify($application->user, 'app.teach.notify_approved', ['url' => url('/teach')]);
    }

    public function rejectApplication(TeacherApplication $application, User $admin, ?string $note): void
    {
        $application->forceFill(['status' => 'rejected', 'admin_note' => $note, 'reviewed_by' => $admin->id, 'reviewed_at' => now()])->save();
        $this->notify($application->user, 'app.teach.notify_rejected', ['url' => route('teach.apply')]);
    }

    public function approveCourse(Course $course): void
    {
        $course->forceFill(['status' => CourseStatus::Published, 'published_at' => $course->published_at ?? now(), 'review_note' => null])->save();
        if ($course->teacher) {
            $this->notify($course->teacher, 'app.teach.notify_published', ['course' => $course->title, 'url' => route('courses.show', $course)]);
        }
    }

    public function requestChanges(Course $course, string $note): void
    {
        $course->forceFill(['status' => CourseStatus::Draft, 'review_note' => $note])->save();
        if ($course->teacher) {
            $this->notify($course->teacher, 'app.teach.notify_changes', ['course' => $course->title, 'note' => $note, 'url' => url('/teach/courses/'.$course->getRouteKey().'/edit')]);
        }
    }

    /** Telegram message in the teacher's language, if they connected Telegram. Never blocks the admin action. */
    private function notify(User $user, string $key, array $replace): void
    {
        if (! $user->telegram_id || ! $this->telegram->configured()) {
            return;
        }
        try {
            $this->telegram->send($user->telegram_id, __($key, $replace, $user->locale));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
