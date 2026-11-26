<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_preview' => 'boolean'];
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(Chapter::class);
    }

    public function minutes(): int
    {
        return max(1, (int) ceil($this->duration_seconds / 60));
    }
}
