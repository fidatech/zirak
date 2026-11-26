<?php

namespace App\Services;

use App\Enums\CertificateType;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;

class CertificateIssuer
{
    public function __construct(private ProgressService $progress) {}

    public function maybeIssue(User $user, Course $course): ?Certificate
    {
        if ($course->certificate_type === CertificateType::None) {
            return null;
        }

        $existing = Certificate::where('user_id', $user->id)->where('course_id', $course->id)->first();
        if ($existing) {
            return $existing;
        }

        $state = $this->progress->courseState($user, $course);
        if (! $state['complete']) {
            return null;
        }

        Enrollment::where('user_id', $user->id)->where('course_id', $course->id)
            ->whereNull('completed_at')->update(['completed_at' => now()]);

        return Certificate::create([
            'code' => $this->newCode(),
            'user_id' => $user->id,
            'course_id' => $course->id,
            'name_on_certificate' => $user->name ?: $user->displayName(),
            'type' => $course->certificate_type,
            'score' => $state['average_score'],
            'issued_at' => now(),
        ]);
    }

    public function newCode(): string
    {
        $alphabet = 'ACDEFGHJKLMNPQRTUVWXY34679';
        do {
            $code = 'ZRK-'.now()->format('y').'-'.collect(range(1, 6))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->implode('');
        } while (Certificate::where('code', $code)->exists());

        return $code;
    }
}
