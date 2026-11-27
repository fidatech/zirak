<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\TeacherApplication;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** The latest things that happened, newest first (like a school's "recent activity"). */
class RecentActivity extends Widget
{
    protected string $view = 'filament.widgets.recent-activity';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $items = new Collection;
        Enrollment::with(['user', 'course'])->latest()->take(6)->get()->each(fn ($e) => $items->push([
            'at' => $e->created_at, 'icon' => '🎓', 'who' => $e->user?->publicName(), 'text' => 'enrolled in', 'what' => $e->course?->title,
        ]));
        Order::with(['user', 'course'])->where('status', OrderStatus::Paid)->latest('paid_at')->take(6)->get()->each(fn ($o) => $items->push([
            'at' => $o->paid_at, 'icon' => '💳', 'who' => $o->user?->publicName(), 'text' => 'paid '.number_format($o->total).' '.$o->currency.' for', 'what' => $o->course?->title,
        ]));
        Certificate::with(['user', 'course'])->latest('issued_at')->take(4)->get()->each(fn ($c) => $items->push([
            'at' => $c->issued_at, 'icon' => '🏅', 'who' => $c->user?->publicName(), 'text' => 'earned a certificate in', 'what' => $c->course?->title,
        ]));
        TeacherApplication::latest()->take(4)->get()->each(fn ($a) => $items->push([
            'at' => $a->created_at, 'icon' => '🧑‍🏫', 'who' => $a->full_name, 'text' => 'applied to teach', 'what' => null,
        ]));
        Course::whereNotNull('submitted_at')->with('teacher')->latest('submitted_at')->take(4)->get()->each(fn ($c) => $items->push([
            'at' => $c->submitted_at, 'icon' => '📝', 'who' => $c->teacher?->publicName(), 'text' => 'sent a course for review:', 'what' => $c->title,
        ]));

        return ['items' => $items->filter(fn ($i) => $i['at'])
            ->map(fn ($i) => ['at' => Carbon::parse($i['at'])] + $i)
            ->sortByDesc('at')->take(8)->values()];
    }
}
