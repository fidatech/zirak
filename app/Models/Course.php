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
}
