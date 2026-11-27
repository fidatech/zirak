<x-layouts.app>
    <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-6 lg:px-8 lg:py-8">
        <div class="flex items-center gap-3">
            <div class="flex-1">
                <h1 class="text-2xl font-extrabold">{{ __('app.home.hello', ['name' => auth()->user()->displayName()]) }}</h1>
                <p class="text-sm text-slate-600">{{ __('app.home.keep_going') }}</p>
            </div>
            @if ($streak > 0)
                <span class="flex items-center gap-1.5 rounded-full bg-orange-100 px-3 py-1.5 text-sm font-bold text-orange-800"><x-svg-icon name="flame" :size="16" class="text-orange-500" />{{ __('app.home.streak', ['count' => n($streak)]) }}</span>
            @endif
        </div>

        @if ($next)
            @php($step = $next['state']['next'])
            <section class="flex items-center gap-4 rounded-3xl bg-brand-600 p-5 text-white md:p-6">
                <div class="flex flex-1 flex-col gap-2">
                    <div class="text-sm font-bold text-brand-100">{{ __('app.home.next_step') }}</div>
                    <div class="text-xl font-extrabold leading-relaxed">{{ $next['course']->title }}{{ sep() }}{{ $step['chapter']->title }}</div>
                    <div class="text-sm text-brand-100">{{ __('app.chapter.'.$step['tab']) }}{{ sep() }}{{ __('app.course.progress') }} {{ pct($next['state']['percent']) }}</div>
                    <x-progress :value="$next['state']['percent']" color="#fff" track="rgba(255,255,255,.28)" class="max-w-md" />
                    <a href="{{ route('chapters.show', [$next['course'], $step['chapter'], $step['tab']]) }}" class="mt-2 self-start rounded-xl bg-white px-5 py-2.5 font-bold text-slate-900">{{ __('app.course.continue') }}</a>
                </div>
                <x-subject-art :category="$next['course']->category" :size="96" class="hidden shrink-0 sm:block" />
            </section>
        @endif

        @if ($myCourses->isNotEmpty())
            <section>
                <h2 class="mb-3 text-lg font-extrabold">{{ __('app.home.my_courses') }}</h2>
                <div class="grid gap-3 md:grid-cols-2">
                    @foreach ($myCourses as $row)
                        <a href="{{ route('courses.show', $row['course']) }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-3">
                            <x-subject-art :category="$row['course']->category" :size="48" class="shrink-0" />
                            <div class="min-w-0 flex-1">
                                <div class="truncate font-bold">{{ $row['course']->title }}</div>
                                <div class="mt-1 flex items-center gap-2 text-xs text-slate-600"><x-progress :value="$row['state']['percent']" class="max-w-40" /> {{ pct($row['state']['percent']) }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <div class="mb-3 flex items-baseline justify-between">
                <h2 class="text-lg font-extrabold">{{ __('app.home.subjects') }}</h2>
                <a href="{{ route('catalog') }}" class="text-sm font-bold text-brand-600">{{ __('app.home.all_subjects') }}</a>
            </div>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                @foreach ($categories as $category)
                    <x-subject-tile :category="$category" />
                @endforeach
            </div>
        </section>

        @if ($weak->isNotEmpty())
            <section class="rounded-3xl border border-slate-200 bg-white p-5">
                <div class="mb-3 flex items-baseline justify-between">
                    <h2 class="font-extrabold">{{ __('app.home.weak_topics') }}</h2>
                    <a href="{{ route('report') }}" class="text-sm font-bold text-brand-600">{{ __('app.home.full_report') }}</a>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($weak as $topic)
                        <a href="{{ route('chapters.show', [$topic['chapter']->course, $topic['chapter'], 'practice']) }}" @class(['rounded-full px-3.5 py-1.5 text-sm', 'bg-amber-100 text-amber-900' => $topic['percent'] < 70, 'bg-green-100 text-green-900' => $topic['percent'] >= 70])>{{ $topic['chapter']->title }}{{ sep() }}{{ pct($topic['percent']) }}</a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($featured->isNotEmpty())
            <section>
                <h2 class="mb-3 text-lg font-extrabold">{{ __('app.home.featured') }}</h2>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    @foreach ($featured as $course)
                        <x-course-card :course="$course" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-layouts.app>
