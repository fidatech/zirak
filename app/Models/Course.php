<?php

namespace App\Models;

use App\Enums\CertificateType;
use App\Enums\CourseLevel;
use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'outcomes' => 'array',
            'is_free' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'level' => CourseLevel::class,
            'status' => CourseStatus::class,
            'certificate_type' => CertificateType::class,
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderBy('sort');
    }

    public function lessons(): HasManyThrough
    {
        return $this->hasManyThrough(Lesson::class, Chapter::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Lessons anyone may open before buying (like Udemy's "Preview").
     * Teachers choose them with "Free preview"; if they chose none, the course's first lesson is the preview.
     *
     * @return list<int>
     */
    public function previewLessonIds(): array
    {
        return $this->previewIds ??= (function () {
            $lessons = Lesson::query()->join('chapters', 'chapters.id', '=', 'lessons.chapter_id')
                ->where('chapters.course_id', $this->id)
                ->orderBy('chapters.sort')->orderBy('chapters.id')->orderBy('lessons.sort')->orderBy('lessons.id')
                ->get(['lessons.id', 'lessons.is_preview']);
            $chosen = $lessons->where('is_preview', true)->pluck('id')->map(fn ($id) => (int) $id)->values()->all();

            return $chosen ?: $lessons->take(1)->pluck('id')->map(fn ($id) => (int) $id)->all();
        })();
    }

    /** @var list<int>|null */
    private ?array $previewIds = null;

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', CourseStatus::Published);
    }

    public function isFreeFor(string $currency = 'AFN'): bool
    {
        return $this->is_free || ($currency === 'AFN' ? $this->price_afn === 0 : $this->price_usd_cents === 0);
    }

    public function languageLabel(): string
    {
        return __('app.lang.'.$this->language);
    }

    public function totalMinutes(): int
    {
        return (int) ceil($this->lessons()->sum('duration_seconds') / 60);
    }

    /** What course cards show: teacher, lesson count and length, student count. */
    public function scopeForCards($query)
    {
        return $query->with(['category', 'teacher'])
            ->withCount(['lessons', 'enrollments'])
            ->withSum('lessons', 'duration_seconds');
    }
}
