<?php

namespace App\Models;

use App\Enums\QuestionKind;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Question extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['options' => 'array', 'kind' => QuestionKind::class];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
