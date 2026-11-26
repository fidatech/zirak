<?php

namespace App\Models;

use App\Enums\UserRole;
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
    protected $fillable = ['name', 'nickname', 'phone', 'email', 'password', 'locale', 'is_private', 'role', 'telegram_reminders', 'data_saver'];

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
        return $this->role === UserRole::Admin;
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

    public function isEnrolledIn(Course $course): bool
    {
        return $this->enrollments()->where('course_id', $course->id)->exists();
    }
}
