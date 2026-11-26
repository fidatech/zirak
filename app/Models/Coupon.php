<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_scholarship' => 'boolean', 'expires_at' => 'datetime'];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function isUsableFor(Course $course): bool
    {
        return $this->is_active
            && ($this->expires_at === null || $this->expires_at->isFuture())
            && ($this->max_uses === null || $this->used_count < $this->max_uses)
            && ($this->course_id === null || $this->course_id === $course->id);
    }

    /** Discount in the same minor unit as $amount. */
    public function discountFor(int $amount): int
    {
        $discount = $this->type === 'fixed' ? $this->value : (int) round($amount * min($this->value, 100) / 100);

        return min($amount, $discount);
    }
}
