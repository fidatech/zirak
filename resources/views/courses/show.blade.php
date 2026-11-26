@php([$soft, $strong, $dark] = $course->category->palette())
<x-layouts.app :title="$course->title">
    <div style="background: {{ $soft }}">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 px-4 py-6 md:flex-row md:items-center lg:px-8">
            <x-subject-art :category="$course->category" :size="88" class="shrink-0" />
            <div class="flex-1">
                <div class="text-sm font-bold" style="color: {{ $dark }}">
                    <a href="{{ route('catalog.category', $course->category) }}">{{ $course->category->name() }}</a> · {{ $course->level->label() }} · {{ $course->languageLabel() }}
                </div>
                <h1 class="mt-1 text-2xl font-extrabold md:text-3xl">{{ $course->title }}</h1>
                @if ($course->subtitle)<p class="mt-1 text-slate-700">{{ $course->subtitle }}</p>@endif
                <div class="mt-2 text-sm text-slate-700">{{ n($course->chapters->count()) }} {{ __('app.chapters') }} · {{ n($lessonCount) }} {{ __('app.lessons') }} · {{ n($minutes) }} {{ __('app.minutes') }}</div>
                @if ($enrolled)
                    <div class="mt-3 flex max-w-sm items-center gap-3 text-sm"><x-progress :value="$state['percent']" :color="$strong" track="#fff" /> {{ pct($state['percent']) }}</div>
                @endif
            </div>
            @if ($enrolled && $state['next'])
                <a href="{{ route('chapters.show', [$course, $state['next']['chapter'], $state['next']['tab']]) }}" class="self-start rounded-2xl bg-slate-900 px-5 py-3 font-bold text-white md:self-center">{{ __('app.course.continue') }}: {{ __('app.chapter.'.$state['next']['tab']) }}</a>
            @endif
        </div>
    </div>

    <div class="mx-auto flex max-w-6xl flex-col gap-6 px-4 py-6 lg:flex-row lg:items-start lg:px-8">
        <div class="flex min-w-0 flex-1 flex-col gap-4">
            @if ($enrolled)
                <h2 class="text-lg font-extrabold">{{ __('app.course.chapters') }}</h2>
                @foreach ($course->chapters as $i => $chapter)
                    @php($s = $state['chapters'][$chapter->id])
                    @php($isCurrent = $state['current']?->is($chapter))
                    @php($nextTab = ! $s['learn_done'] ? 'learn' : (! $s['practice_done'] ? 'practice' : 'test'))
                    <div @class(['rounded-2xl bg-white p-4', 'border-2 border-brand-600 ring-4 ring-brand-600/10' => $isCurrent, 'border border-slate-200' => ! $isCurrent])>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div @class(['text-xs font-bold', 'text-green-700' => $s['complete'], 'text-brand-600' => $isCurrent && ! $s['complete'], 'text-slate-500' => ! $isCurrent && ! $s['complete']])>
                                    {{ __('app.chapter.chapter_n', ['n' => n($i + 1)]) }}@if($s['complete']) · {{ __('app.chapter.done') }}@elseif($isCurrent) · {{ __('app.chapter.in_progress') }}@endif
                                </div>
                                <div class="text-lg font-extrabold">{{ $chapter->title }}</div>
                            </div>
                            <span class="text-sm text-slate-500">{{ n($s['lessons_total']) }} {{ __('app.lessons') }}</span>
                        </div>
                        <div class="mt-3 grid gap-2 sm:grid-cols-3">
                            @foreach ([['learn', $s['learn_done'], n($s['lessons_done']).'/'.n($s['lessons_total'])], ['practice', $s['practice_done'], n($s['practice_correct']).'/'.n($s['practice_total'])], ['test', $s['passed'], $s['best_score'] !== null ? pct($s['best_score']) : null]] as $k => [$tab, $ok, $meta])
                                @php($locked = $tab === 'test' && ! $s['test_unlocked'])
                                @if ($locked)
                                    <span class="flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-2.5 text-sm text-slate-500"><x-svg-icon name="lock" :size="16" />{{ n($k + 1) }} · {{ __('app.chapter.test') }} ({{ __('app.chapter.locked_test') }})</span>
                                @else
                                    <a href="{{ route('chapters.show', [$course, $chapter, $tab]) }}" @class(['flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-bold', 'bg-green-100 text-green-900' => $ok, 'bg-brand-600 text-white' => ! $ok && $isCurrent && $tab === $nextTab, 'border border-slate-200 bg-slate-50 text-slate-700' => ! $ok && ! ($isCurrent && $tab === $nextTab)])>
                                        @if($ok)<x-svg-icon name="check" :size="16" />@endif {{ n($k + 1) }} · {{ __('app.chapter.'.$tab) }}
                                        @if($meta)<span class="ms-auto font-normal opacity-80">{{ $meta }}</span>@endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div class="flex items-center gap-4 rounded-2xl bg-slate-900 p-4 text-white">
                    <x-svg-icon name="award" :size="36" class="shrink-0 text-amber-300" />
                    <div class="flex-1">
                        <div class="font-extrabold">{{ __('app.course.final') }}</div>
                        <div class="text-sm text-slate-300">{{ __('app.course.final_text', ['mark' => pct(config('zirak.pass_mark'))]) }}</div>
                    </div>
                    @if ($certificate)
                        <a href="{{ route('certificates.show', $certificate) }}" class="rounded-xl bg-amber-300 px-4 py-2 font-bold text-slate-900">{{ __('app.cert.title') }}</a>
                    @endif
                </div>
            @else
                @if ($course->outcomes)
                    <section class="rounded-2xl border border-slate-200 bg-white p-5">
                        <h2 class="mb-3 text-lg font-extrabold">{{ __('app.course.you_learn') }}</h2>
                        <ul class="grid gap-2 sm:grid-cols-2">
                            @foreach ($course->outcomes as $outcome)
                                <li class="flex gap-2"><x-svg-icon name="check" :size="18" class="mt-1 shrink-0 text-brand-600" />{{ $outcome }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif
                @if ($course->description)
                    <section class="prose-lesson rounded-2xl border border-slate-200 bg-white p-5">{!! Str::markdown($course->description, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</section>
                @endif
                <section class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h2 class="mb-3 text-lg font-extrabold">{{ __('app.course.content') }}</h2>
                    <div class="overflow-hidden rounded-xl border border-slate-200">
                        @foreach ($course->chapters as $i => $chapter)
                            <div class="flex justify-between bg-slate-50 px-4 py-3 font-bold @if($i) border-t border-slate-200 @endif">
                                <span>{{ __('app.chapter.chapter_n', ['n' => n($i + 1)]) }}: {{ $chapter->title }}</span>
                                <span class="text-sm font-normal text-slate-500">{{ n($chapter->lessons->count()) }} {{ __('app.lessons') }}</span>
                            </div>
                            @foreach ($chapter->lessons as $lesson)
                                <div class="flex justify-between border-t border-slate-100 px-4 py-2.5 text-sm">
                                    <span>{{ $lesson->title }}</span>
                                    @if ($lesson->is_preview && auth()->check())
                                        <a href="{{ route('chapters.show', [$course, $chapter, 'learn', 'lesson' => $lesson->id]) }}" class="font-bold text-brand-600">{{ __('app.course.preview') }}</a>
                                    @else
                                        <span class="text-slate-500">{{ n($lesson->minutes()) }} {{ __('app.minutes') }}</span>
                                    @endif
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </section>
                @if ($course->instructor_name)
                    <section class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full text-xl font-extrabold" style="background: {{ $soft }}; color: {{ $strong }}">{{ mb_substr($course->instructor_name, 0, 1) }}</span>
                        <div><div class="text-sm text-slate-500">{{ __('app.course.instructor') }}</div><div class="font-bold">{{ $course->instructor_name }}</div>@if($course->instructor_bio)<div class="text-sm text-slate-600">{{ $course->instructor_bio }}</div>@endif</div>
                    </section>
                @endif
            @endif
        </div>

        <aside class="flex w-full flex-col gap-4 lg:sticky lg:top-6 lg:w-80 lg:shrink-0">
            @if ($enrolled)
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="mb-2 font-extrabold">{{ __('app.course.your_scores') }}</div>
                    <div class="flex justify-between py-1 text-sm"><span class="text-slate-600">{{ __('app.course.progress') }}</span><strong>{{ pct($state['percent']) }}</strong></div>
                    <div class="flex justify-between py-1 text-sm"><span class="text-slate-600">{{ __('app.course.avg_score') }}</span><strong>{{ $state['average_score'] !== null ? pct($state['average_score']) : '—' }}</strong></div>
                </div>
                <div class="rounded-2xl border border-brand-300 bg-brand-50 p-5">
                    <div class="font-extrabold">{{ __('app.chapter.ask') }}</div>
                    <p class="mt-1 text-sm text-brand-900">{{ __('app.chapter.ask_text') }}</p>
                </div>
            @else
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-3 p-5">
                        <div class="text-3xl font-extrabold">{{ $course->is_free ? __('app.free') : afn($course->price_afn) }}</div>
                        @auth
                            <form method="POST" action="{{ route('courses.purchase', $course) }}" class="flex flex-col gap-3">
                                @csrf
                                @unless ($course->is_free)
                                    <label class="text-sm text-slate-700">{{ __('app.course.coupon') }}
                                        <input name="coupon" value="{{ old('coupon') }}" class="mt-1 h-11 w-full rounded-xl border border-slate-300 px-3 uppercase" dir="ltr">
                                    </label>
                                    @error('coupon')<div class="text-sm text-red-700">{{ $message }}</div>@enderror
                                @endunless
                                <button class="rounded-xl bg-brand-600 py-3.5 text-lg font-bold text-white">{{ $course->is_free ? __('app.course.enroll_free') : __('app.course.buy') }}</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="rounded-xl bg-brand-600 py-3.5 text-center text-lg font-bold text-white">{{ __('app.course.login_to_buy') }}</a>
                        @endauth
                        <div class="border-t border-slate-100 pt-3 text-sm text-slate-700">
                            <div class="mb-1 font-bold">{{ __('app.course.includes') }}</div>
                            <ul class="flex flex-col gap-1.5">
                                <li>{{ __('app.course.inc_lessons', ['count' => n($lessonCount)]) }}</li>
                                <li>{{ __('app.course.inc_practice') }}</li>
                                @if ($course->certificate_type->value !== 'none')<li>{{ __('app.course.inc_certificate') }}</li>@endif
                                <li>{{ __('app.course.inc_lifetime') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </aside>
    </div>
</x-layouts.app>
