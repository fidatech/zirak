<?php

namespace App\Services;

use App\Enums\CourseStatus;
use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payments\CheckoutService;

/** Zirak Plus: a paid period that opens every published course. */
class SubscriptionService
{
    public function __construct(private CheckoutService $checkout, private EnrollmentService $enrollments) {}

    public function enabled(): bool
    {
        return (bool) config('zirak.subscription.enabled') && config('zirak.subscription.price_afn') > 0;
    }

    /** Create a pending plan order and return the payment page URL. */
    public function start(User $user): string
    {
        abort_unless($this->enabled(), 404);
        $gateway = $this->checkout->gateway();
        $price = (int) config('zirak.subscription.price_afn');

        $order = Order::create([
            'kind' => 'subscription',
            'user_id' => $user->id,
            'course_id' => null,
            'status' => OrderStatus::Pending,
            'gateway' => $gateway->key(),
            'currency' => $gateway->currency(),
            'subtotal' => $price,
            'total' => $price,
        ]);

        return $gateway->createCheckout($order);
    }

    /**
     * Called from EnrollmentService::markPaid inside its locked transaction. One period per order:
     * it starts now, or when the student's current paid time ends.
     */
    public function activate(Order $order): Subscription
    {
        $existing = Subscription::where('order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        $start = $order->user->subscriptions()->where('ends_at', '>', now())->max('ends_at');
        $start = $start ? now()->parse($start)->max(now()) : now();

        return Subscription::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'starts_at' => $start,
            'ends_at' => $start->copy()->addDays((int) config('zirak.subscription.days')),
        ]);
    }

    /** A subscriber opens a course with their plan (no payment). */
    public function useFor(User $user, Course $course): ?Enrollment
    {
        if (! $user->hasActivePlan() || $course->status !== CourseStatus::Published) {
            return null;
        }

        $enrollment = $this->enrollments->enroll($user, $course, 'subscription');

        return $enrollment;
    }
}
