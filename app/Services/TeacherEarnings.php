<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Course;
use App\Models\Order;
use App\Models\TeacherPayout;
use App\Models\User;
use Illuminate\Support\Collection;

/** Teacher money: shares are fixed on the order when it is paid; refunded orders stop counting. */
class TeacherEarnings
{
    public static function shareFor(Course $course): int
    {
        return (int) ($course->teacher_share ?? config('zirak.teacher_share'));
    }

    /** Called inside EnrollmentService::markPaid (order row is locked). */
    public static function snapshot(Order $order): void
    {
        $course = $order->course;
        if (! $course?->teacher_id) {
            return;
        }
        $share = self::shareFor($course);
        $order->forceFill([
            'teacher_id' => $course->teacher_id,
            'teacher_share' => $share,
            'teacher_amount' => intdiv($order->total * $share + 50, 100),
        ]);
    }

    /** @return Collection<string, array{earned:int, paid_out:int, balance:int}> keyed by currency */
    public function summary(User $teacher): Collection
    {
        $earned = Order::where('teacher_id', $teacher->id)->where('status', OrderStatus::Paid)
            ->groupBy('currency')->selectRaw('currency, SUM(teacher_amount) as total')->pluck('total', 'currency');
        $paid = TeacherPayout::where('teacher_id', $teacher->id)
            ->groupBy('currency')->selectRaw('currency, SUM(amount) as total')->pluck('total', 'currency');

        return $earned->keys()->merge($paid->keys())->unique()->sort()->values()
            ->mapWithKeys(fn ($c) => [$c => [
                'earned' => (int) ($earned[$c] ?? 0),
                'paid_out' => (int) ($paid[$c] ?? 0),
                'balance' => (int) ($earned[$c] ?? 0) - (int) ($paid[$c] ?? 0),
            ]]);
    }

    public function balance(User $teacher, string $currency): int
    {
        return $this->summary($teacher)[$currency]['balance'] ?? 0;
    }

    public function thisMonth(User $teacher): Collection
    {
        return Order::where('teacher_id', $teacher->id)->where('status', OrderStatus::Paid)
            ->where('paid_at', '>=', now()->startOfMonth())
            ->groupBy('currency')->selectRaw('currency, SUM(teacher_amount) as total, COUNT(*) as sales')->get()
            ->keyBy('currency');
    }

    /** Formats integer money: AFN as whole afghanis, USD from cents. */
    public static function money(int $amount, string $currency): string
    {
        return $currency === 'USD' ? '$'.number_format($amount / 100, 2) : number_format($amount).' AFN';
    }
}
