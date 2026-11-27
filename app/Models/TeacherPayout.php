<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherPayout extends Model
{
    public const METHODS = ['hesabpay' => 'HesabPay', 'bank' => 'Bank transfer', 'cash' => 'Cash', 'other' => 'Other'];

    protected $fillable = ['teacher_id', 'currency', 'amount', 'method', 'reference', 'note', 'paid_on', 'created_by'];

    protected function casts(): array
    {
        return ['paid_on' => 'date', 'amount' => 'integer'];
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
