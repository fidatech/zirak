<?php

namespace App\Models;

use App\Enums\QuestionKind;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort');
    }

    public function practiceQuestions(): HasMany
    {
        return $this->questions()->where('kind', QuestionKind::Practice);
    }

    public function testQuestions(): HasMany
    {
        return $this->questions()->where('kind', QuestionKind::Test);
    }
}
