@php([$soft, $strong, $dark] = $course->category->palette())
@php($guestTop = auth()->check() ? 'pt-8' : 'pt-28 lg:pt-32')
@php($teacherName = $course->teacher?->publicName() ?? $course->instructor_name)
<x-public-page :title="$course->title">
    {{-- ================= Dark header (Udemy-style) ================= --}}
    <section class="relative overflow-hidden bg-ink text-white {{ $guestTop }} pb-10 {{ auth()->check() ? 'lg:min-h-[20rem]' : 'lg:min-h-[27rem]' }}">
        <div aria-hidden="true" class="pointer-events-none absolute -start-20 -top-20 h-72 w-72 rounded-full bg-brand-600/40 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute bottom-0 end-1/3 h-56 w-56 rounded-full bg-accent-500/25 blur-3xl"></div>
        <div class="relative mx-auto flex max-w-6xl flex-col gap-3 px-5 lg:pe-[24rem]">
            <nav class="flex flex-wrap items-center gap-2 text-sm font-semibold text-brand-200">
                <a href="{{ route('catalog') }}" class="hover:text-white">{{ __('app.nav.courses') }}</a><span class="opacity-50">/</span>
                <a href="{{ route('catalog.category', $course->category) }}" class="hover:text-white">{{ $course->category->name() }}</a>
            </nav>
            <div class="flex flex-wrap gap-2">
                @if ($course->is_featured)<span class="rounded-full bg-amber-300 px-3 py-0.5 text-xs font-black text-amber-950">★ {{ __('app.courseui.pick') }}</span>@endif
                @if ($enrolled)<span class="rounded-full bg-emerald-400/20 px-3 py-0.5 text-xs font-bold text-emerald-200">✓ {{ __('app.courseui.enrolled') }}</span>@endif
            </div>
            <h1 class="text-3xl font-black leading-[1.4] sm:text-4xl">{{ $course->title }}</h1>
            @if ($course->subtitle)<p class="text-lg leading-8 text-slate-300">{{ $course->subtitle }}</p>@endif
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-300">
                <span class="flex items-center gap-1.5"><x-svg-icon name="chart" :size="16" />{{ $course->level->label() }}</span>
                <span class="flex items-center gap-1.5"><x-svg-icon name="globe" :size="16" />{{ $course->languageLabel() }}</span>
                @if ($students)<span class="flex items-center gap-1.5"><x-svg-icon name="user" :size="16" />{{ __('app.courseui.students', ['n' => n($students)]) }}</span>@endif
                @if ($course->published_at)<span class="flex items-center gap-1.5"><x-svg-icon name="calendar" :size="16" />{{ __('app.courseui.updated', ['date' => n($course->updated_at->translatedFormat('F Y'))]) }}</span>@endif
            </div>
            @if ($teacherName)
                <div class="text-sm text-slate-300">{{ __('app.course.instructor') }}:
                    @if ($course->teacher)<a href="{{ route('teachers.show', $course->teacher) }}" class="font-bold text-brand-200 underline-offset-4 hover:underline">{{ $teacherName }}</a>@else<span class="font-bold text-white">{{ $teacherName }}</span>@endif
                </div>
            @endif
            @if ($enrolled)
                <div class="mt-2 flex max-w-md items-center gap-3 text-sm"><x-progress :value="$state['percent']" track="rgba(255,255,255,.15)" /> <strong>{{ pct($state['percent']) }}</strong></div>
                @if ($state['next'])
                    <a href="{{ route('chapters.show', [$course, $state['next']['chapter'], $state['next']['tab']]) }}" class="btn-primary mt-1 self-start rounded-xl px-6 py-3 font-bold">{{ __('app.course.continue') }}: {{ __('app.chapter.'.$state['next']['tab']) }}</a>
                @endif
            @endif
        </div>
    </section>

    <div class="mx-auto flex max-w-6xl flex-col gap-8 px-5 py-8 lg:flex-row-reverse lg:items-start">
        {{-- ================= Sticky side card ================= --}}
        <aside class="flex w-full flex-col gap-4 lg:sticky lg:top-24 lg:-mt-64 lg:w-[22rem] lg:shrink-0">
            @if ($enrolled)
                <div class="shadow-soft rounded-2xl bg-white p-5 ring-1 ring-slate-100">
                    <div class="mb-3 font-black">{{ __('app.course.your_scores') }}</div>
                    <div class="flex items-center gap-4">
                        <x-ring :value="$state['percent']" :size="76" :stroke="9" />
                        <div class="flex flex-1 flex-col gap-1 text-sm">
                            <div class="flex justify-between"><span class="text-slate-500">{{ __('app.course.progress') }}</span><strong>{{ pct($state['percent']) }}</strong></div>
                            <div class="flex justify-between"><span class="text-slate-500">{{ __('app.course.avg_score') }}</span><strong>{{ $state['average_score'] !== null ? pct($state['average_score']) : '—' }}</strong></div>
                        </div>
                    </div>
                </div>
                <x-tutor-panel :course="$course" />
            @else
                <div id="buy" class="shadow-soft scroll-mt-24 overflow-hidden rounded-3xl bg-white ring-1 ring-slate-100">
                    <div class="hidden h-40 items-center justify-center lg:flex" style="background: radial-gradient(circle at 30% 20%, #fff 0, transparent 55%), {{ $soft }}">
                        <x-subject-art :category="$course->category" :size="88" />
                    </div>
                    <div class="flex flex-col gap-4 p-5">
                        <div class="text-3xl font-black">{{ $course->is_free ? __('app.free') : afn($course->price_afn) }}</div>
                        @auth
                            <form method="POST" action="{{ route('courses.purchase', $course) }}" class="flex flex-col gap-3">
                                @csrf
                                @unless ($course->is_free)
                                    <label class="text-sm text-slate-600">{{ __('app.course.coupon') }}
                                        <input name="coupon" value="{{ old('coupon') }}" class="mt-1 h-11 w-full rounded-xl border border-slate-200 px-3 uppercase focus:border-brand-500 focus:outline-none" dir="ltr">
                                    </label>
                                    @error('coupon')<div class="text-sm text-red-700">{{ $message }}</div>@enderror
                                @endunless
                                <button class="btn-primary rounded-xl py-3.5 text-lg font-bold">{{ $course->is_free ? __('app.course.enroll_free') : __('app.course.buy') }}</button>
                            </form>
                        @else
                            <a href="{{ route('register', ['next' => '/courses/'.$course->getRouteKey()]) }}" class="btn-primary rounded-xl py-3.5 text-center text-lg font-bold">{{ __('app.course.login_to_buy') }}</a>
                        @endauth
                        @if ($firstPreview)
                            <a href="{{ route('chapters.show', [$course, $firstPreview->chapter_id, 'learn', 'lesson' => $firstPreview->id]) }}" class="flex items-center justify-center gap-2 rounded-xl border border-slate-200 py-3 font-bold text-ink hover:bg-slate-50">
                                <x-svg-icon name="play" :size="18" class="text-brand-600" />{{ __('app.preview.watch') }}
                            </a>
                        @endif
                        <div class="border-t border-slate-100 pt-4 text-sm">
                            <div class="mb-3 font-black">{{ __('app.course.includes') }}</div>
                            <ul class="flex flex-col gap-2.5 text-slate-700">
                                <li class="flex items-center gap-2.5"><x-svg-icon name="play" :size="18" class="text-brand-600" />{{ __('app.courseui.inc_time', ['time' => duration($minutes)]) }}</li>
                                <li class="flex items-center gap-2.5"><x-svg-icon name="book" :size="18" class="text-brand-600" />{{ __('app.course.inc_lessons', ['count' => n($lessonCount)]) }}</li>
                                @if ($questionCount)<li class="flex items-center gap-2.5"><x-svg-icon name="pencil" :size="18" class="text-brand-600" />{{ __('app.courseui.inc_questions', ['n' => n($questionCount)]) }}</li>@endif
                                <li class="flex items-center gap-2.5"><x-svg-icon name="chat" :size="18" class="text-brand-600" />{{ __('app.courseui.inc_tutor') }}</li>
                                <li class="flex items-center gap-2.5"><x-svg-icon name="download" :size="18" class="text-brand-600" />{{ __('app.courseui.inc_offline') }}</li>
                                @if ($course->certificate_type->value !== 'none')<li class="flex items-center gap-2.5"><x-svg-icon name="award" :size="18" class="text-brand-600" />{{ __('app.course.inc_certificate') }}</li>@endif
                                <li class="flex items-center gap-2.5"><x-svg-icon name="check" :size="18" class="text-brand-600" />{{ __('app.course.inc_lifetime') }}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </aside>

        {{-- ================= Main column ================= --}}
        <div class="flex min-w-0 flex-1 flex-col gap-6">
            @if ($enrolled)
                <h2 class="text-xl font-black">{{ __('app.course.chapters') }}</h2>
                @foreach ($course->chapters as $i => $chapter)
                    @php($s = $state['chapters'][$chapter->id])
                    @php($isCurrent = $state['current']?->is($chapter))
                    @php($nextTab = ! $s['learn_done'] ? 'learn' : (! $s['practice_done'] ? 'practice' : 'test'))
                    <div @class(['shadow-soft rounded-2xl bg-white p-5', 'ring-2 ring-brand-500' => $isCurrent, 'ring-1 ring-slate-100' => ! $isCurrent])>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div @class(['text-xs font-bold', 'text-green-700' => $s['complete'], 'text-brand-600' => $isCurrent && ! $s['complete'], 'text-slate-500' => ! $isCurrent && ! $s['complete']])>
                                    {{ __('app.chapter.chapter_n', ['n' => n($i + 1)]) }}@if($s['complete']){{ sep() }}{{ __('app.chapter.done') }}@elseif($isCurrent){{ sep() }}{{ __('app.chapter.in_progress') }}@endif
                                </div>
                                <div class="text-lg font-extrabold">{{ $chapter->title }}</div>
                            </div>
                            <span class="text-sm text-slate-500">{{ n($s['lessons_total']) }} {{ __('app.lessons') }}</span>
                        </div>
                        <div class="mt-3 grid gap-2 sm:grid-cols-3">
                            @foreach ([['learn', $s['learn_done'], n($s['lessons_done']).'/'.n($s['lessons_total'])], ['practice', $s['practice_done'], n($s['practice_correct']).'/'.n($s['practice_total'])], ['test', $s['passed'], $s['best_score'] !== null ? pct($s['best_score']) : null]] as $k => [$tab, $ok, $meta])
                                @php($locked = $tab === 'test' && ! $s['test_unlocked'])
                                @if ($locked)
                                    <span class="flex items-center gap-2 rounded-xl bg-slate-100 px-3 py-2.5 text-sm text-slate-500"><x-svg-icon name="lock" :size="16" />{{ n($k + 1) }}{{ sep() }}{{ __('app.chapter.test') }} ({{ __('app.chapter.locked_test') }})</span>
                                @else
                                    <a href="{{ route('chapters.show', [$course, $chapter, $tab]) }}" @class(['flex items-center gap-2 rounded-xl px-3 py-2.5 text-sm font-bold', 'bg-green-100 text-green-900' => $ok, 'bg-brand-600 text-white' => ! $ok && $isCurrent && $tab === $nextTab, 'border border-slate-200 bg-slate-50 text-slate-700' => ! $ok && ! ($isCurrent && $tab === $nextTab)])>
                                        @if($ok)<x-svg-icon name="check" :size="16" />@endif {{ n($k + 1) }}{{ sep() }}{{ __('app.chapter.'.$tab) }}
                                        @if($meta)<span class="ms-auto font-normal opacity-80">{{ $meta }}</span>@endif
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <div class="shadow-soft flex items-center gap-4 rounded-2xl bg-ink p-5 text-white">
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
                    <section class="rounded-2xl border border-slate-200 bg-white p-6">
                        <h2 class="mb-4 text-xl font-black">{{ __('app.course.you_learn') }}</h2>
                        <ul class="grid gap-3 sm:grid-cols-2">
                            @foreach ($course->outcomes as $outcome)
                                <li class="flex gap-2.5 leading-7"><x-svg-icon name="check" :size="18" class="mt-1.5 shrink-0 text-brand-600" />{{ $outcome }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                <section>
                    <h2 class="text-xl font-black">{{ __('app.course.content') }}</h2>
                    <p class="mb-4 mt-1 text-sm text-slate-500">{{ __('app.courseui.curriculum_meta', ['chapters' => n($course->chapters->count()), 'lessons' => n($lessonCount), 'time' => duration($minutes)]) }}</p>
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white">
                        @foreach ($course->chapters as $i => $chapter)
                            <details @class(['group', 'border-t border-slate-200' => $i]) @if($i === 0) open @endif>
                                <summary class="flex cursor-pointer list-none items-center gap-3 bg-slate-50 px-5 py-4 font-bold hover:bg-slate-100">
                                    <span class="text-slate-400 transition group-open:rotate-90 rtl:-scale-x-100"><x-svg-icon name="forward" :size="16" /></span>
                                    <span class="flex-1">{{ __('app.chapter.chapter_n', ['n' => n($i + 1)]) }}: {{ $chapter->title }}</span>
                                    <span class="shrink-0 text-sm font-normal text-slate-500">{{ n($chapter->lessons->count()) }} {{ __('app.lessons') }}{{ sep() }}{{ duration((int) ceil($chapter->lessons->sum('duration_seconds') / 60)) }}</span>
                                </summary>
                                @foreach ($chapter->lessons as $lesson)
                                    <div class="flex items-center gap-3 border-t border-slate-100 px-5 py-3 text-sm">
                                        <x-svg-icon :name="$lesson->video_id ? 'play' : 'book'" :size="16" class="shrink-0 text-slate-400" />
                                        @if ($enrolled || in_array($lesson->id, $previewIds))
                                            <a href="{{ route('chapters.show', [$course, $chapter, 'learn', 'lesson' => $lesson->id]) }}" class="flex-1 hover:text-brand-600">{{ $lesson->title }}</a>
                                        @else
                                            <span class="flex-1">{{ $lesson->title }}</span>
                                        @endif
                                        @if ($enrolled || in_array($lesson->id, $previewIds))
                                            <a href="{{ route('chapters.show', [$course, $chapter, 'learn', 'lesson' => $lesson->id]) }}" class="font-bold text-brand-600 underline-offset-4 hover:underline">{{ $enrolled ? __('app.preview.open') : __('app.course.preview') }}</a>
                                        @else
                                            <x-svg-icon name="lock" :size="14" class="shrink-0 text-slate-400" />
                                        @endif
                                        <span class="shrink-0 text-slate-500">{{ duration($lesson->minutes()) }}</span>
                                    </div>
                                @endforeach
                            </details>
                        @endforeach
                    </div>
                </section>

                @if ($course->description)
                    <section>
                        <h2 class="mb-3 text-xl font-black">{{ __('app.courseui.about') }}</h2>
                        <div class="prose-lesson leading-8 text-slate-700">{!! Str::markdown($course->description, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                    </section>
                @endif

                @if ($teacherName)
                    <section>
                        <h2 class="mb-3 text-xl font-black">{{ __('app.course.instructor') }}</h2>
                        @if ($course->teacher)
                            <a href="{{ route('teachers.show', $course->teacher) }}" class="shadow-soft flex items-center gap-4 rounded-2xl bg-white p-5 ring-1 ring-slate-100 hover:ring-brand-200">
                                <span class="bg-brand-gradient flex h-16 w-16 shrink-0 items-center justify-center rounded-full text-white"><x-svg-icon name="user" :size="30" /></span>
                                <div class="min-w-0 flex-1"><div class="text-lg font-black">{{ $teacherName }}</div>@if($course->teacher->teacher_headline)<div class="text-sm text-slate-600">{{ $course->teacher->teacher_headline }}</div>@endif</div>
                                <x-svg-icon name="forward" :size="18" class="text-slate-400" />
                            </a>
                        @else
                            <div class="shadow-soft flex items-center gap-4 rounded-2xl bg-white p-5 ring-1 ring-slate-100">
                                <span class="bg-brand-gradient flex h-16 w-16 shrink-0 items-center justify-center rounded-full text-white"><x-svg-icon name="user" :size="30" /></span>
                                <div><div class="text-lg font-black">{{ $teacherName }}</div>@if($course->instructor_bio)<div class="text-sm text-slate-600">{{ $course->instructor_bio }}</div>@endif</div>
                            </div>
                        @endif
                    </section>
                @endif
            @endif
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="border-t border-slate-200/70 bg-white">
            <div class="mx-auto max-w-6xl px-5 py-12">
                <h2 class="mb-6 text-2xl font-black">{{ __('app.courseui.related', ['category' => $course->category->name()]) }}</h2>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:gap-5">
                    @foreach ($related as $item)
                        <x-course-card :course="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-public-page>
