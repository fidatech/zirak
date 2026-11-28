<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Zirak Plus: the monthly all-access plan. */
class PlanController extends Controller
{
    public function show(Request $request, SubscriptionService $plans): View
    {
        abort_unless($plans->enabled(), 404);
        $published = Course::published();

        return view('plans.show', [
            'price' => (int) config('zirak.subscription.price_afn'),
            'days' => (int) config('zirak.subscription.days'),
            'endsAt' => $request->user()?->planEndsAt(),
            'courseCount' => (clone $published)->count(),
            'paidValue' => (int) (clone $published)->where('is_free', false)->sum('price_afn'),
            'courses' => (clone $published)->forCards()->orderByDesc('is_featured')->take(6)->get(),
        ]);
    }

    public function subscribe(Request $request, SubscriptionService $plans): RedirectResponse
    {
        return redirect()->away($plans->start($request->user()));
    }
}
