<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['status' => OrderStatus::class, 'paid_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->number ??= 'ZRK-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function isSubscription(): bool
    {
        return $this->kind === 'subscription';
    }

    /** What the student is paying for, as shown at checkout. */
    public function itemName(): string
    {
        return $this->isSubscription() ? __('app.plan.item_name', ['days' => n(config('zirak.subscription.days'))]) : (string) $this->course?->title;
    }

    /** Where to send the student after paying. */
    public function itemUrl(): string
    {
        return $this->isSubscription() || ! $this->course ? route('plans') : route('courses.show', $this->course);
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::Paid;
    }

    /** Amount in major units (AFN or USD) for display / gateways. */
    public function amountMajor(): float
    {
        return $this->currency === 'USD' ? $this->total / 100 : (float) $this->total;
    }
}
