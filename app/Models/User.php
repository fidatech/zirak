<?php

namespace App\Models;

use App\Enums\UserRole;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    // "role" is fillable for the admin panel only; student-facing controllers pass validated fields that never include it.
    protected $fillable = ['name', 'nickname', 'phone', 'email', 'password', 'locale', 'is_private', 'role', 'telegram_reminders', 'data_saver', 'teacher_headline', 'teacher_bio'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_active_at' => 'datetime',
            'password' => 'hashed',
            'is_private' => 'boolean',
            'data_saver' => 'boolean',
            'telegram_linked_at' => 'datetime',
            'telegram_reminded_at' => 'datetime',
            'telegram_reminders' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'teach' => $this->isTeacher(),
            default => $this->role === UserRole::Admin,
        };
    }

    public function isTeacher(): bool
    {
        return $this->role === UserRole::Instructor;
    }

    /** Name shown to other people: never the phone number. */
    public function publicName(): string
    {
        return $this->nickname ?: ($this->name ?: __('app.student'));
    }

    public function taughtCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    public function teacherApplications(): HasMany
    {
        return $this->hasMany(TeacherApplication::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function displayName(): string
    {
        return $this->nickname ?: ($this->name ?: __('app.student'));
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')->withTimestamps();
    }

    public function lessonCompletions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function practiceAnswers(): HasMany
    {
        return $this->hasMany(PracticeAnswer::class);
    }

    public function testAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Bought, free or admin enrollments are kept forever; ones opened with Zirak Plus need an active plan. */
    public function isEnrolledIn(Course $course): bool
    {
        $source = $this->enrollments()->where('course_id', $course->id)->value('source');

        return $source !== null && ($source !== 'subscription' || $this->hasActivePlan());
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasActivePlan(): bool
    {
        return $this->planEndsAt() !== null;
    }

    /** When the student's paid plan time runs out (counting periods paid in advance), or null if no active plan. */
    public function planEndsAt(): ?CarbonInterface
    {
        // Periods are always chained end to end, so the plan runs until the latest end, once one has started.
        if (! $this->subscriptions()->active()->exists()) {
            return null;
        }

        return $this->subscriptions()->latest('ends_at')->first()?->ends_at;
    }
}
