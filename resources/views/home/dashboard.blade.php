@php($user = auth()->user())
@php($avg = $myCourses->isNotEmpty() ? (int) round($myCourses->avg(fn ($r) => $r['state']['percent'])) : 0)
@php($trend = fn (?int $n) => $n === null ? null : ($n > 0 ? '+'.n($n) : n($n)))
<x-layouts.app>
    <div class="mx-auto flex max-w-7xl flex-col gap-5 px-4 py-5 lg:px-8 lg:py-7">

        {{-- Stat cards --}}
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-4">
            <x-stat-card emoji="📚" tint="bg-brand-50" :label="__('app.dash.stat_lessons')" :value="n($stats['lessons_total'])"
                :trend="$stats['lessons_trend']" :trend-text="__('app.dash.vs_week', ['n' => $trend($stats['lessons_trend'])])" />
            <x-stat-card emoji="🎯" tint="bg-emerald-50" :label="__('app.dash.stat_accuracy')" :value="$stats['accuracy'] === null ? '—' : pct($stats['accuracy'])"
                :trend="$stats['accuracy_trend']" :trend-text="$stats['accuracy'] === null ? __('app.dash.no_practice') : ($stats['accuracy_trend'] === null ? null : __('app.dash.vs_week', ['n' => $trend($stats['accuracy_trend'])]))" />
            <x-stat-card emoji="🔥" tint="bg-orange-50" :label="__('app.dash.stat_streak')" :value="n($streak)"
                :trend-text="__('app.dash.days_active', ['n' => n($stats['active_days'])])" />
            <x-stat-card emoji="🏅" tint="bg-amber-50" :label="__('app.dash.stat_certs')" :value="n($stats['certificates'])"
                :trend-text="$stats['certificates'] ? null : __('app.dash.certs_hint')" />
        </div>

        <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_360px]">
            <div class="flex min-w-0 flex-col gap-5">
                {{-- Welcome banner --}}
                <section class="relative overflow-hidden rounded-2xl bg-gradient-to-l from-brand-100 via-white to-pink-50 p-5 ring-1 ring-slate-100 shadow-soft">
                    <div class="relative z-10 max-w-[75%]">
                        <h1 class="text-xl font-black sm:text-2xl">{{ __('app.dash.welcome', ['name' => $user->displayName()]) }}</h1>
                        <p class="mt-1 text-sm text-slate-600 sm:text-base">{{ __('app.dash.welcome_text') }}</p>
                    </div>
                    <div aria-hidden="true" class="absolute -bottom-2 end-4 text-7xl drop-shadow-sm sm:text-8xl">🧑‍🎓</div>
                </section>

                {{-- Continue learning --}}
                @if ($next)
                    @php($step = $next['state']['next'])
                    <x-panel :title="__('app.dash.continue')" icon="play">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            @php([$soft] = $next['course']->category->palette())
                            <div class="flex h-28 w-full shrink-0 items-center justify-center rounded-xl sm:w-44" style="background: radial-gradient(circle at 30% 20%, #fff 0, transparent 55%), {{ $soft }}">
                                <x-subject-art :category="$next['course']->category" :size="64" />
                            </div>
                            <div class="flex min-w-0 flex-1 flex-col gap-1.5">
                                <div class="text-lg font-black leading-snug">{{ $next['course']->title }}</div>
                                <div class="text-sm text-slate-600">{{ $step['chapter']->title }}</div>
                                <div class="flex items-center gap-2 text-xs font-semibold text-brand-700"><x-svg-icon name="sparkle" :size="14" />{{ __('app.dash.step_label', ['step' => __('app.chapter.'.$step['tab'])]) }}</div>
                                <div class="flex items-center gap-2"><x-progress :value="$next['state']['percent']" class="max-w-xs" /><span class="text-xs font-bold text-slate-500">{{ pct($next['state']['percent']) }}</span></div>
                            </div>
                            <a href="{{ route('chapters.show', [$next['course'], $step['chapter'], $step['tab']]) }}" class="btn-primary self-start rounded-xl px-5 py-2.5 text-center font-bold sm:self-center">{{ __('app.course.continue') }}</a>
                        </div>
                    </x-panel>
                @elseif ($myCourses->isEmpty())
                    <section class="bg-brand-gradient relative overflow-hidden rounded-2xl p-6 text-white shadow-soft">
                        <div aria-hidden="true" class="absolute -end-10 -top-10 h-40 w-40 rounded-full bg-white/15 blur-2xl"></div>
                        <h2 class="relative text-xl font-black">{{ __('app.dash.empty_title') }}</h2>
                        <p class="relative mt-1 text-white/90">{{ __('app.dash.empty_text') }}</p>
                        <a href="{{ route('catalog') }}" class="relative mt-4 inline-block rounded-xl bg-white px-5 py-2.5 font-bold text-ink">{{ __('app.dash.browse') }}</a>
                    </section>
                @endif

                {{-- My courses --}}
                @if ($myCourses->isNotEmpty())
                    <x-panel :title="__('app.home.my_courses')" icon="book" :link="route('catalog')" :link-text="__('app.dash.browse')">
                        <div class="flex flex-col divide-y divide-slate-100">
                            @foreach ($myCourses as $row)
                                @php([$soft] = $row['course']->category->palette())
                                <a href="{{ route('courses.show', $row['course']) }}" class="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl" style="background: {{ $soft }}"><x-subject-art :category="$row['course']->category" :size="32" /></span>
                                    <div class="min-w-0 flex-1">
                                        <div class="truncate font-bold">{{ $row['course']->title }}</div>
                                        <div class="mt-1.5 flex items-center gap-2"><x-progress :value="$row['state']['percent']" class="max-w-48" /><span class="text-xs font-bold text-slate-500">{{ pct($row['state']['percent']) }}</span></div>
                                    </div>
                                    <x-svg-icon name="forward" :size="18" class="text-slate-300" />
                                </a>
                            @endforeach
                        </div>
                    </x-panel>
                @endif

                {{-- This week --}}
                @php($max = max(1, $stats['week']->max('count')))
                <x-panel :title="__('app.dash.activity')" icon="calendar">
                    <p class="-mt-2 mb-4 text-sm text-slate-500">{{ __('app.dash.activity_hint') }}</p>
                    <div class="flex h-36 items-end gap-1 sm:gap-4">
                        @foreach ($stats['week'] as $day)
                            @php($isToday = $day['date']->isToday())
                            <div class="flex min-w-0 flex-1 flex-col items-center gap-2">
                                <span class="text-xs font-bold text-slate-500">{{ $day['count'] ? n($day['count']) : '' }}</span>
                                <div class="flex h-24 w-full items-end justify-center">
                                    <div @class(['w-full max-w-10 rounded-t-lg', 'bg-brand-gradient' => $day['count'], 'bg-slate-100' => ! $day['count']]) style="height: {{ $day['count'] ? max(10, 100 * $day['count'] / $max) : 8 }}%"></div>
                                </div>
                                <span @class(['max-w-full truncate rounded-md px-1 text-[10px] sm:px-1.5 sm:text-xs', 'bg-brand-600 font-bold text-white' => $isToday, 'text-slate-500' => ! $isToday])>{{ $day['date']->translatedFormat('D') }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-panel>
            </div>

            {{-- Right column --}}
            <div class="flex flex-col gap-5">
                <x-panel :title="__('app.dash.progress_title')" icon="chart">
                    <div class="flex items-center gap-4">
                        <x-ring :value="$avg" />
                        <p class="text-sm leading-7 text-slate-600">{{ __('app.dash.progress_text', ['n' => n($myCourses->count())]) }}</p>
                    </div>
                </x-panel>
                <x-panel :title="__('app.dash.accuracy_title')" icon="check">
                    <div class="flex items-center gap-4">
                        <x-ring :value="$stats['accuracy'] ?? 0" />
                        <p class="text-sm leading-7 text-slate-600">{{ $stats['accuracy'] === null ? __('app.dash.accuracy_empty') : __('app.dash.accuracy_text', ['n' => n($stats['answered'])]) }}</p>
                    </div>
                </x-panel>
                @if ($weak->isNotEmpty())
                    <x-panel :title="__('app.home.weak_topics')" icon="pencil" :link="route('report')" :link-text="__('app.home.full_report')">
                        <div class="flex flex-col gap-2">
                            @foreach ($weak as $topic)
                                <a href="{{ route('chapters.show', [$topic['chapter']->course, $topic['chapter'], 'practice']) }}" class="flex items-center justify-between gap-3 rounded-xl bg-slate-50 px-3 py-2.5 text-sm hover:bg-brand-50">
                                    <span class="truncate font-semibold">{{ $topic['chapter']->title }}</span>
                                    <span @class(['shrink-0 rounded-full px-2 py-0.5 text-xs font-bold', 'bg-amber-100 text-amber-800' => $topic['percent'] < 70, 'bg-emerald-100 text-emerald-800' => $topic['percent'] >= 70])>{{ pct($topic['percent']) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </x-panel>
                @endif
                <x-panel :title="__('app.home.subjects')" icon="grid" :link="route('catalog')" :link-text="__('app.dash.see_all')">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($categories as $category)
                            @php([$soft, , $dark] = $category->palette())
                            <a href="{{ route('catalog.category', $category) }}" class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-semibold" style="background: {{ $soft }}; color: {{ $dark }}">{{ $category->name() }}</a>
                        @endforeach
                    </div>
                </x-panel>
            </div>
        </div>

        @if ($featured->isNotEmpty())
            <section>
                <div class="mb-3 flex items-baseline justify-between">
                    <h2 class="text-lg font-black">{{ __('app.home.featured') }}</h2>
                    <a href="{{ route('catalog') }}" class="text-sm font-semibold text-brand-600">{{ __('app.dash.see_all') }}</a>
                </div>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:gap-4">
                    @foreach ($featured as $course)
                        <x-course-card :course="$course" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
