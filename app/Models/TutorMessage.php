<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TutorMessage extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['helpful' => 'boolean', 'escalated_at' => 'datetime', 'answered_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(self::class, 'question_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(self::class, 'question_id');
    }

    /** Student questions waiting for a teacher. */
    public function scopeWaitingForTeacher(Builder $query): Builder
    {
        return $query->where('role', 'student')->whereNotNull('escalated_at')->whereNull('answered_at');
    }

    /** Shape sent to the chat widget. Never includes phone numbers or other students' data. */
    public function toChat(): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'content' => $this->content,
            'escalated' => $this->escalated_at !== null,
            'answered' => $this->answered_at !== null,
            'helpful' => $this->helpful,
        ];
    }
}
