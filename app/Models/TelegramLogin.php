<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramLogin extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function findByToken(?string $token): ?self
    {
        return $token ? static::where('token_hash', hash('sha256', $token))->first() : null;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
