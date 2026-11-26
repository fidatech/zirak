<?php

namespace App\Services\Payments;

use App\Enums\OrderStatus;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use App\Services\EnrollmentService;

class CheckoutService
{
    public function __construct(private EnrollmentService $enrollments) {}

    public function gateway(): PaymentGateway
    {
        $driver = config('services.hesabpay.driver');

        if ($driver === 'fake' && app()->environment('production')) {
            throw new \RuntimeException('The fake payment gateway cannot run in production.');
        }

        return $driver === 'fake' ? app(FakeGateway::class) : app(HesabPayGateway::class);
    }

    /**
     * Start buying a course. Returns a URL to redirect to.
     */
    public function start(User $user, Course $course, ?Coupon $coupon = null): string
    {
        if ($user->isEnrolledIn($course)) {
            return route('courses.show', $course);
        }

        if ($course->is_free) {
            $this->enrollments->enroll($user, $course, 'free');

            return route('courses.show', $course);
        }

        $gateway = $this->gateway();
        $subtotal = $course->price_afn;
        $discount = $coupon?->discountFor($subtotal) ?? 0;

        $order = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'coupon_id' => $coupon?->id,
            'status' => OrderStatus::Pending,
            'gateway' => $discount >= $subtotal ? 'coupon' : $gateway->key(),
            'currency' => $gateway->currency(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $subtotal - $discount,
        ]);

        if ($order->total === 0) {
            $this->enrollments->markPaid($order);

            return route('courses.show', $course);
        }

        return $gateway->createCheckout($order);
    }
}
