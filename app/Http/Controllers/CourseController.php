<?php

namespace App\Http\Controllers;

use App\Enums\CourseStatus;
use App\Models\Certificate;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Question;
use App\Services\Payments\CheckoutService;
use App\Services\ProgressService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function show(Request $request, Course $course, ProgressService $progress): View
    {
        abort_unless($course->status === CourseStatus::Published || $request->user()?->isAdmin()
            || ($course->teacher_id && $course->teacher_id === $request->user()?->id), 404); // teachers can preview their own drafts

        $course->load(['category', 'chapters.lessons', 'teacher']);
        $user = $request->user();
        $enrolled = $user?->isEnrolledIn($course) ?? false;

        return view('courses.show', [
            'course' => $course,
            'enrolled' => $enrolled,
            'state' => $enrolled ? $progress->courseState($user, $course) : null,
            'certificate' => $enrolled ? Certificate::where('user_id', $user->id)->where('course_id', $course->id)->first() : null,
            'lessonCount' => $course->chapters->sum(fn ($c) => $c->lessons->count()),
            'minutes' => (int) ceil($course->chapters->sum(fn ($c) => $c->lessons->sum('duration_seconds')) / 60),
            'students' => $course->enrollments()->count(),
            'onPlan' => $onPlan = ($user?->hasActivePlan() ?? false),
            'planOffer' => ! $enrolled && ! $course->is_free && ! $onPlan && app(SubscriptionService::class)->enabled(),
            'planExpired' => ! $enrolled && $user && $user->enrollments()->where('course_id', $course->id)->where('source', 'subscription')->exists(),
            'previewIds' => $previewIds = $course->previewLessonIds(),
            'firstPreview' => $enrolled ? null : $course->chapters->flatMap->lessons->firstWhere('id', $previewIds[0] ?? 0),
            'questionCount' => Question::whereIn('chapter_id', $course->chapters->pluck('id'))->count(),
            'related' => Course::published()->forCards()->where('category_id', $course->category_id)->whereKeyNot($course->id)
                ->orderByDesc('is_featured')->take(4)->get(),
        ]);
    }

    public function purchase(Request $request, Course $course, CheckoutService $checkout): RedirectResponse
    {
        abort_unless($course->status === CourseStatus::Published, 404);
        $request->validate(['coupon' => ['nullable', 'string', 'max:40']]);

        $coupon = null;
        if ($code = trim((string) $request->input('coupon'))) {
            $coupon = Coupon::where('code', strtoupper($code))->first();
            if (! $coupon || ! $coupon->isUsableFor($course)) {
                throw ValidationException::withMessages(['coupon' => __('app.course.coupon_invalid')]);
            }
        }

        $url = $checkout->start($request->user(), $course, $coupon);

        if ($request->user()->isEnrolledIn($course)) {
            session()->flash('status', __('app.course.enrolled'));
        }

        return redirect()->away($url);
    }
}
