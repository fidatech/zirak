<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherPoolEarning extends Model
{
    protected $fillable = ['teacher_id', 'month', 'currency', 'lessons', 'total_lessons', 'pool', 'amount'];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
