{{-- Home page for visitors (Sample D): AI classes by level first, then the course marketplace. --}}
@php
    $telegram = config('services.telegram.bot_username') ? 'https://t.me/'.config('services.telegram.bot_username') : null;
    $planOn = config('zirak.subscription.enabled');
    $planPrice = afn(config('zirak.subscription.price_afn'));
    $planDays = n(config('zirak.subscription.days'));
@endphp
<x-layouts.app landing>
    <x-site-nav />

    {{-- ================= HERO ================= --}}
    <section class="bg-brand-600 text-white">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-5 py-12 lg:grid-cols-[1.15fr_1fr] lg:gap-14 lg:py-16">
            <div class="flex flex-col gap-5">
                <span class="self-start rounded-lg bg-accent-400 px-3 py-1.5 text-sm font-extrabold text-accent-900">{{ __('app.home2.badge') }}</span>
                <h1 class="text-4xl font-black leading-[1.15] sm:text-5xl lg:text-[3.4rem]">{{ __('app.home2.h1', ['max' => n(max(1, $levels->max('number') ?? 5))]) }}</h1>
                <p class="max-w-xl text-lg leading-8 text-white/90">{{ __('app.home2.lead') }}</p>
                <ul class="flex flex-col gap-2.5 text-base">
                    @foreach (['check1', 'check2', 'check3'] as $k)
                        <li class="flex items-center gap-3"><span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-accent-400 text-accent-900"><x-svg-icon name="check" :size="14" /></span>{{ __('app.home2.'.$k) }}</li>
                    @endforeach
                </ul>
                @if ($levels->isNotEmpty())
                    <a href="#levels" class="flex items-center gap-2 self-start border-b-2 border-accent-400 pb-0.5 font-bold text-white">{{ __('app.home2.see_levels', ['count' => n($levels->count())]) }}<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg></a>
                @endif
            </div>
            @include('partials.level-finder', ['selected' => 1])
        </div>
    </section>

    {{-- ================= THE LEVELS ================= --}}
    @if ($levels->isNotEmpty())
        <section id="levels" class="scroll-mt-24 bg-brand-50">
            <div class="mx-auto max-w-6xl px-5 py-14 lg:py-16">
                <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                    <div class="max-w-2xl">
                        <div class="mb-2 text-sm font-extrabold uppercase tracking-wider text-brand-600">{{ __('app.levels.kicker') }}</div>
                        <h2 class="text-3xl font-black leading-tight sm:text-4xl">{{ __('app.levels.title', ['count' => n($levels->count())]) }}</h2>
                        <p class="mt-3 text-lg text-muted">{{ __('app.levels.text') }}</p>
                    </div>
                    <a href="{{ route('levels') }}" class="btn-outline rounded-xl px-5 py-3 font-bold">{{ __('app.levels.compare') }}</a>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                    @foreach ($levels as $level)<x-level-card :level="$level" />@endforeach
                </div>
            </div>
        </section>

        {{-- ================= INSIDE LEVEL 1 ================= --}}
        <section class="mx-auto grid max-w-6xl items-start gap-10 px-5 py-14 lg:grid-cols-2 lg:py-16">
            <div class="flex flex-col gap-4">
                <div class="text-sm font-extrabold uppercase tracking-wider text-brand-600">{{ __('app.levels.level_n', ['n' => n($first->number)]) }}{{ sep() }}{{ $first->text('title') }}</div>
                <h2 class="text-3xl font-black leading-tight">{{ __('app.levels.inside_title') }}</h2>
                <p class="text-lg leading-8 text-muted">{{ $first->text('who') }} {{ $first->text('after') }}</p>
                <div class="flex flex-wrap gap-2 text-sm font-semibold">
                    @if ($firstLessons->isNotEmpty())<span class="flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-2"><x-svg-icon name="play" :size="15" class="text-brand-600" />{{ __('app.levels.n_classes', ['count' => n($firstCourse->chapters->sum(fn ($c) => $c->lessons->count()))]) }}</span>@endif
                    <span class="flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-2"><x-svg-icon name="pencil" :size="15" class="text-brand-600" />{{ __('app.levels.practice') }}</span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-2"><x-svg-icon name="chart" :size="15" class="text-brand-600" />{{ __('app.levels.test') }}</span>
                    <span class="flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-2"><x-svg-icon name="award" :size="15" class="text-brand-600" />{{ __('app.levels.cert_level') }}</span>
                    @if ($first->text('duration'))<span class="flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-2"><x-svg-icon name="calendar" :size="15" class="text-brand-600" />{{ $first->text('duration') }}</span>@endif
                </div>
                <div class="mt-2 flex flex-wrap gap-3">
                    <a href="{{ $first->url() }}" class="btn-primary rounded-xl px-6 py-3.5 font-bold">{{ __('app.levels.start_level', ['n' => n($first->number)]) }}</a>
                </div>
            </div>
            <div class="rounded-2xl border border-line bg-white px-5 py-2 sm:px-6">
                @if ($firstLessons->isNotEmpty())
                    <div class="flex items-center justify-between py-3 font-extrabold"><span>{{ __('app.levels.classes_in') }}</span></div>
                    <ol>
                        @foreach ($firstLessons as $i => $lesson)
                            @php($open = in_array($lesson->id, $previewIds, true))
                            <li @class(['flex items-center gap-3 py-3', 'border-t border-line' => $i])>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-sm font-extrabold text-brand-600">{{ n($i + 1) }}</span>
                                @if ($open)
                                    <a href="{{ route('chapters.show', [$firstCourse, $lesson->chapter_id, 'learn', 'lesson' => $lesson->id]) }}" class="flex-1 font-semibold hover:text-brand-600">{{ $lesson->title }}</a>
                                    <span class="text-xs font-bold text-brand-600">{{ __('app.levels.free_preview') }}</span><x-svg-icon name="play" :size="15" class="text-brand-600" />
                                @else
                                    <span class="flex-1 font-semibold">{{ $lesson->title }}</span><x-svg-icon name="lock" :size="15" class="text-muted" />
                                @endif
                            </li>
                        @endforeach
                    </ol>
                @else
                    <div class="py-3 font-extrabold">{{ __('app.levels.what_you_learn') }}</div>
                    <ol>
                        @foreach ($first->learnList() as $i => $item)
                            <li @class(['flex items-center gap-3 py-3', 'border-t border-line' => $i])>
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-sm font-extrabold text-brand-600">{{ n($i + 1) }}</span>
                                <span class="flex-1 font-semibold">{{ $item }}</span>
                            </li>
                        @endforeach
                    </ol>
                    <p class="border-t border-line py-3 text-sm text-muted">{{ __('app.levels.soon') }}</p>
                @endif
            </div>
        </section>
    @endif

    {{-- ================= HOW EVERY CLASS WORKS ================= --}}
    <section class="border-t border-line">
        <div class="mx-auto max-w-6xl px-5 py-14 lg:py-16">
            <h2 class="mb-8 text-3xl font-black">{{ __('app.home2.how_title') }}</h2>
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['play', '#E7E0FA'], ['pencil', '#FFF3CC'], ['chart', '#DDE9FB'], ['award', '#E1F3E8']] as $i => [$icon, $tint])
                    <div class="flex flex-col items-start gap-3">
                        <span class="flex h-13 w-13 items-center justify-center rounded-2xl text-ink" style="background: {{ $tint }}"><x-svg-icon :name="$icon" :size="24" /></span>
                        <div class="text-lg font-extrabold">{{ n($i + 1) }}. {{ __('app.home2.how'.($i + 1).'_t') }}</div>
                        <p class="leading-7 text-muted">{{ __('app.home2.how'.($i + 1)) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= MARKETPLACE ================= --}}
    <section class="bg-brand-50">
        <div class="mx-auto max-w-6xl px-5 py-14 lg:py-16">
            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <h2 class="text-3xl font-black">{{ __('app.home2.market_title') }}</h2>
                    <p class="mt-2 text-lg text-muted">{{ __('app.home2.market_text') }}</p>
                </div>
                <a href="{{ route('catalog') }}" class="btn-outline rounded-xl px-5 py-3 font-bold">{{ __('app.home2.browse_all') }}</a>
            </div>
            @if ($categories->where('courses_count', '>', 0)->isNotEmpty())
                <div class="mb-6 flex gap-2 overflow-x-auto pb-1">
                    @foreach ($categories->where('courses_count', '>', 0) as $category)
                        <a href="{{ route('catalog.category', $category) }}" class="shrink-0 rounded-full border border-line bg-white px-4 py-2 text-sm font-semibold hover:border-brand-300 hover:text-brand-700">{{ $category->name() }}</a>
                    @endforeach
                </div>
            @endif
            @if ($featured->isNotEmpty())
                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($featured->take(8) as $course)<x-course-card :course="$course" />@endforeach
                </div>
            @else
                <p class="rounded-2xl border border-dashed border-brand-300 bg-white p-6 text-center text-muted">{{ __('app.home2.market_empty') }}</p>
            @endif
        </div>
    </section>

    {{-- ================= WHY ZIRAK ================= --}}
    <section id="why">
        <div class="mx-auto max-w-6xl px-5 py-14 lg:py-16">
            <h2 class="mb-8 text-3xl font-black">{{ __('app.home2.why_title') }}</h2>
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['globe', 'chat', 'cloud-off', 'award'] as $i => $icon)
                    <div class="flex flex-col gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600"><x-svg-icon :name="$icon" :size="22" /></span>
                        <div class="text-lg font-extrabold">{{ __('app.home2.why'.($i + 1).'_t') }}</div>
                        <p class="leading-7 text-muted">{{ __('app.home2.why'.($i + 1)) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================= ZIRAK PLUS + TEACH ================= --}}
    <section class="mx-auto grid max-w-6xl gap-5 px-5 pb-14 lg:grid-cols-[1.2fr_1fr] lg:pb-16">
        @if ($planOn)
            <div class="flex flex-col gap-3 rounded-2xl bg-brand-600 p-8 text-white sm:p-9">
                <div class="text-sm font-extrabold uppercase tracking-wider text-white/85">{{ __('app.plan.name') }}</div>
                <div class="text-2xl font-black leading-snug sm:text-3xl">{{ __('app.home2.plus_title', ['price' => $planPrice, 'days' => $planDays]) }}</div>
                <p class="leading-7 text-white/90">{{ __('app.home2.plus_text') }}</p>
                <a href="{{ route('plans') }}" class="btn-accent mt-2 self-start rounded-xl px-6 py-3 font-extrabold">{{ __('app.plan.subscribe') }}</a>
            </div>
        @endif
        <div id="teach" class="flex flex-col gap-3 rounded-2xl bg-accent-100 p-8 sm:p-9">
            <div class="text-sm font-extrabold uppercase tracking-wider text-brand-600">{{ __('app.home2.teachers_menu') }}</div>
            <div class="text-2xl font-black leading-snug sm:text-3xl">{{ __('app.home2.teach_title') }}</div>
            <p class="leading-7 text-muted">{{ __('app.home2.teach_text') }}</p>
            <a href="{{ auth()->check() ? route('teach.apply') : route('register', ['as' => 'teacher']) }}" class="btn-primary mt-2 self-start rounded-xl px-6 py-3 font-bold">{{ __('app.home2.apply') }}</a>
        </div>
    </section>

    {{-- ================= HELP ================= --}}
    @if ($telegram)
        <section class="bg-brand-800 text-white">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-6 px-5 py-10">
                <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white text-brand-600"><x-svg-icon name="chat" :size="28" /></span>
                <div class="min-w-0 flex-1">
                    <div class="text-2xl font-black">{{ __('app.home2.help_title') }}</div>
                    <div class="mt-1 text-white/85">{{ __('app.home2.help_text') }}</div>
                </div>
                <a href="{{ $telegram }}" rel="noopener" class="flex items-center gap-2 rounded-xl bg-white px-6 py-3.5 font-bold text-brand-700 hover:bg-brand-50"><x-svg-icon name="send" :size="18" />{{ __('app.home2.help_btn') }}</a>
            </div>
        </section>
    @endif

    <x-site-footer />
</x-layouts.app>
