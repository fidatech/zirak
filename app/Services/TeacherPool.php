<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\LessonCompletion;
use App\Models\Order;
use App\Models\TeacherPoolEarning;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Shares one month of Zirak Plus money with teachers.
 *
 * pool   = plan money paid that month × teachers' share (%)
 * weight = lessons completed that month, in courses opened with the plan, per course teacher
 * Zirak's own courses (no teacher) count in the total too, so their part of the pool stays with Zirak.
 */
class TeacherPool
{
    /** @return array<int, int> teacher_id => amount (for the given currency) */
    public function compute(string $month): array
    {
        $from = CarbonImmutable::createFromFormat('Y-m-d H:i:s', $month.'-01 00:00:00')->startOfMonth();
        $to = $from->addMonth();
        $result = [];

        DB::transaction(function () use ($month, $from, $to, &$result) {
            TeacherPoolEarning::where('month', $month)->delete();

            $revenue = Order::where('kind', 'subscription')->where('status', OrderStatus::Paid)
                ->whereBetween('paid_at', [$from, $to->subSecond()])
                ->groupBy('currency')->selectRaw('currency, SUM(total) as total')->pluck('total', 'currency');

            $lessons = LessonCompletion::query()
                ->join('lessons', 'lessons.id', '=', 'lesson_completions.lesson_id')
                ->join('chapters', 'chapters.id', '=', 'lessons.chapter_id')
                ->join('courses', 'courses.id', '=', 'chapters.course_id')
                ->join('enrollments', fn ($j) => $j->on('enrollments.course_id', '=', 'courses.id')->on('enrollments.user_id', '=', 'lesson_completions.user_id'))
                ->where('enrollments.source', 'subscription')
                ->whereBetween('lesson_completions.completed_at', [$from, $to->subSecond()])
                ->groupBy('courses.teacher_id')
                ->selectRaw('courses.teacher_id as teacher_id, COUNT(*) as total')
                ->get();
            $all = (int) $lessons->sum('total');

            foreach ($revenue as $currency => $money) {
                $pool = intdiv((int) $money * (int) config('zirak.subscription.teacher_share') + 50, 100);
                if ($pool === 0 || $all === 0) {
                    continue;
                }
                foreach ($lessons->whereNotNull('teacher_id') as $row) {
                    $amount = intdiv($pool * (int) $row->total, $all); // rounded down; the remainder stays with Zirak
                    TeacherPoolEarning::create([
                        'teacher_id' => $row->teacher_id, 'month' => $month, 'currency' => $currency,
                        'lessons' => $row->total, 'total_lessons' => $all, 'pool' => $pool, 'amount' => $amount,
                    ]);
                    $result[(int) $row->teacher_id] = $amount;
                }
            }
        });

        return $result;
    }
}
