<?php

namespace App\Models;

use App\Enums\CourseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One step of the Zirak AI path (Level 1 → 5), described in Dari, Pashto and English. */
class AiLevel extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['content' => 'array', 'is_active' => 'boolean'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('number');
    }

    /** Text in the current language, falling back to Dari, then English. */
    public function text(string $field): string
    {
        $content = $this->content ?? [];
        foreach ([app()->getLocale(), 'prs', 'en'] as $locale) {
            if (filled($value = $content[$locale][$field] ?? null)) {
                return trim((string) $value);
            }
        }

        return '';
    }

    /** @return list<string> */
    public function learnList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', $this->text('learn')))));
    }

    /** "Free", a price, or null when there is nothing to show yet. Uses the linked course when there is one. */
    public function priceLabel(): ?string
    {
        if ($this->course && $this->course->status === CourseStatus::Published) {
            return $this->course->is_free ? __('app.levels.free') : afn($this->course->price_afn);
        }

        return match (true) {
            $this->price_afn === null => null,
            $this->price_afn === 0 => __('app.levels.free'),
            default => afn($this->price_afn),
        };
    }

    public function url(): string
    {
        return $this->course && $this->course->status === CourseStatus::Published
            ? route('courses.show', $this->course)
            : route('levels').'#level-'.$this->number;
    }
}
