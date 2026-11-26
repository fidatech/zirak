<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAttempt extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['answers' => 'array', 'passed' => 'boolean'];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }
}
