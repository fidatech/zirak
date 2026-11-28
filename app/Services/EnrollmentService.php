<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EnrollmentService
{
    public function enroll(User $user, Course $course, string $source, ?Order $order = null): Enrollment
    {
        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['source' => $source, 'order_id' => $order?->id],
        );

        // Opened with Zirak Plus, then bought (or given): now it is theirs to keep, progress included.
        if ($enrollment->source === 'subscription' && $source !== 'subscription') {
            $enrollment->update(['source' => $source, 'order_id' => $order?->id]);
        }

        return $enrollment;
    }

    /**
     * Mark an order paid and give access. Safe to call more than once.
     */
    public function markPaid(Order $order, ?string $transactionId = null): Order
    {
        return DB::transaction(function () use ($order, $transactionId) {
            $order = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->isPaid()) {
                return $order;
            }

            $order->forceFill([
                'status' => OrderStatus::Paid,
                'paid_at' => now(),
                'gateway_transaction_id' => $transactionId ?? $order->gateway_transaction_id,
                'failure_reason' => null,
            ]);
            TeacherEarnings::snapshot($order);
            $order->save();

            $order->coupon?->increment('used_count');
            if ($order->isSubscription()) {
                app(SubscriptionService::class)->activate($order);
            } else {
                $this->enroll($order->user, $order->course, 'order', $order);
            }

            return $order;
        });
    }
}
