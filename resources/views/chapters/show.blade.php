@php([$soft, $strong, $dark] = $course->category->palette())
@php($chapterIndex = $chapters->search(fn ($c) => $c->is($chapter)))
@php($nextChapter = $chapters->get($chapterIndex + 1))
<x-layouts.app :title="$chapter->title">
    <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-3 md:flex-row md:items-center lg:px-8">
            <div class="flex flex-1 items-center gap-3">
                <a href="{{ route('courses.show', $course) }}" aria-label="{{ __('app.back') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-slate-700"><x-svg-icon name="back" :size="18" /></a>
                <div class="min-w-0">
                    <div class="truncate text-xs text-slate-600">{{ $course->title }} · {{ __('app.chapter.chapter_n', ['n' => n($chapterIndex + 1)]) }}</div>
                    <div class="truncate font-extrabold">{{ $chapter->title }}</div>
                </div>
            </div>
            @if ($enrolled)
                <nav class="grid grid-cols-3 gap-1 rounded-xl bg-slate-100 p-1 text-sm" aria-label="{{ $chapter->title }}">
                    @foreach (['learn', 'practice', 'test'] as $i => $t)
                        @php($done = [$state['learn_done'], $state['practice_done'], $state['passed']][$i])
                        <a href="{{ route('chapters.show', [$course, $chapter, $t]) }}" @if($tab === $t) aria-current="page" @endif @class(['flex items-center justify-center gap-1 rounded-lg px-4 py-2', 'bg-brand-600 font-bold text-white' => $tab === $t, 'text-slate-700' => $tab !== $t])>
                            @if($done)<x-svg-icon name="check" :size="14" />@elseif($t === 'test' && ! $state['test_unlocked'])<x-svg-icon name="lock" :size="14" />@endif
                            {{ n($i + 1) }} · {{ __('app.chapter.'.$t) }}
                        </a>
                    @endforeach
                </nav>
            @endif
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-5 lg:px-8">
        @if ($tab === 'learn')
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start">
                <div class="flex min-w-0 flex-1 flex-col gap-4">
                    @if ($lesson)
                        @if ($lesson->video_id && config('services.bunny.library_id'))
                            <div class="aspect-video overflow-hidden rounded-2xl bg-slate-900">
                                <iframe src="https://iframe.mediadelivery.net/embed/{{ config('services.bunny.library_id') }}/{{ $lesson->video_id }}?autoplay=false&preload=false" loading="lazy" class="h-full w-full border-0" allow="accelerometer; gyroscope; encrypted-media; picture-in-picture" allowfullscreen title="{{ $lesson->title }}"></iframe>
                            </div>
                        @else
                            <div class="flex aspect-video max-h-80 flex-col items-center justify-center gap-3 rounded-2xl text-center" style="background: {{ $soft }}">
                                <x-subject-art :category="$course->category" :size="96" />
                                <span class="text-sm" style="color: {{ $dark }}">{{ __('app.chapter.video_soon') }}</span>
                            </div>
                        @endif
                        <h1 class="text-2xl font-extrabold">{{ $lesson->title }}</h1>
                        @if ($lesson->body)
                            <div class="prose-lesson max-w-3xl text-[16px] leading-loose text-slate-800">{!! Str::markdown($lesson->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}</div>
                        @endif
                        @if ($lesson->prompt)
                            <div class="max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white" x-data="copy">
                                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-2">
                                    <span class="text-sm font-bold text-slate-700">{{ __('app.chapter.prompt') }}</span>
                                    <button type="button" class="rounded-lg border border-slate-300 bg-white px-3 py-1 text-sm font-bold text-brand-600" @click="copy($refs.prompt.innerText)" x-text="copied ? @js(__('app.chapter.copied')) : @js(__('app.chapter.copy'))">{{ __('app.chapter.copy') }}</button>
                                </div>
                                <div class="whitespace-pre-line p-4 leading-loose" x-ref="prompt">{{ $lesson->prompt }}</div>
                            </div>
                        @endif
                        @if ($lesson->task)
                            <div class="flex max-w-3xl gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-amber-900">
                                <x-svg-icon name="pencil" class="mt-1 shrink-0" />
                                <div class="leading-loose"><strong>{{ __('app.chapter.your_turn') }}</strong> {{ $lesson->task }}</div>
                            </div>
                        @endif
                        @if ($enrolled)
                            <div class="flex flex-wrap gap-3">
                                @if (in_array($lesson->id, $completed))
                                    <span class="flex items-center gap-2 rounded-xl bg-green-100 px-4 py-3 font-bold text-green-900"><x-svg-icon name="check" :size="18" />{{ __('app.chapter.completed') }}</span>
                                @endif
                                <form method="POST" action="{{ route('lessons.complete', [$course, $chapter, $lesson]) }}">@csrf
                                    <button class="rounded-xl bg-brand-600 px-6 py-3 font-bold text-white">{{ __('app.chapter.mark_complete') }}</button>
                                </form>
                            </div>
                        @endif
                    @endif
                </div>
                <aside class="w-full rounded-2xl border border-slate-200 bg-white p-3 lg:w-80 lg:shrink-0">
                    <div class="px-2 pb-2 font-extrabold">{{ __('app.chapter.learn') }} · {{ n($state['lessons_done']) }}/{{ n($state['lessons_total']) }}</div>
                    @foreach ($lessons as $i => $l)
                        <a href="{{ route('chapters.show', [$course, $chapter, 'learn', 'lesson' => $l->id]) }}" @if($lesson?->is($l)) aria-current="page" @endif @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm', 'bg-brand-100 font-bold text-brand-600' => $lesson?->is($l), 'text-slate-700 hover:bg-slate-50' => ! $lesson?->is($l)])>
                            @if (in_array($l->id, $completed))
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-600 text-white"><x-svg-icon name="check" :size="13" /></span>
                            @else
                                <span class="h-6 w-6 shrink-0 rounded-full border-2 border-slate-300"></span>
                            @endif
                            <span class="flex-1">{{ n($i + 1) }}. {{ $l->title }}</span>
                            <span class="text-xs text-slate-500">{{ n($l->minutes()) }}</span>
                        </a>
                    @endforeach
                </aside>
            </div>
        @elseif ($tab === 'practice')
            <div class="mx-auto max-w-2xl">
                @if ($practice->isEmpty())
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-600">{{ __('app.chapter.no_practice') }}</div>
                    <a href="{{ route('chapters.show', [$course, $chapter, 'test']) }}" class="mt-4 block rounded-xl bg-brand-600 py-3 text-center font-bold text-white">{{ __('app.chapter.go_test') }}</a>
                @else
                    <div x-data="practice({ questions: @js($practice), start: {{ $startIndex }}, correct: {{ $state['practice_correct'] }}, url: @js(route('practice.answer', [$course, $chapter])) })" class="rounded-3xl border border-slate-200 bg-white p-5 md:p-6">
                        <template x-if="! finished">
                            <div class="flex flex-col gap-4">
                                <div class="text-sm text-slate-600" x-text="@js(__('app.chapter.question_n', ['n' => '__N__', 'total' => '__T__'])).replace('__N__', (index + 1).toLocaleString('{{ app()->getLocale() === 'en' ? 'en' : 'fa' }}')).replace('__T__', questions.length.toLocaleString('{{ app()->getLocale() === 'en' ? 'en' : 'fa' }}'))"></div>
                                <div class="text-xl font-extrabold leading-loose" x-text="question.body"></div>
                                <div class="flex flex-col gap-2.5">
                                    <template x-for="(option, i) in question.options" :key="i">
                                        <button type="button" @click="choose(i)" :disabled="result !== null || busy" class="flex items-center gap-3 rounded-2xl border-2 px-4 py-3.5 text-start text-[16px] transition" :class="optionClass(i)">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-sm font-bold" x-text="['{{ text_dir() === 'rtl' ? 'الف' : 'A' }}','{{ text_dir() === 'rtl' ? 'ب' : 'B' }}','{{ text_dir() === 'rtl' ? 'ج' : 'C' }}','{{ text_dir() === 'rtl' ? 'د' : 'D' }}','5','6'][i]"></span>
                                            <span class="flex-1" x-text="option"></span>
                                        </button>
                                    </template>
                                </div>
                                <template x-if="result">
                                    <div class="flex flex-col gap-3">
                                        <div class="rounded-2xl border p-4 leading-loose" :class="result.correct ? 'border-green-200 bg-green-50 text-green-900' : 'border-red-200 bg-red-50 text-red-900'">
                                            <strong x-text="result.correct ? @js(__('app.chapter.correct')) : @js(__('app.chapter.wrong'))"></strong>
                                            <span x-text="result.explanation"></span>
                                        </div>
                                        <button type="button" @click="next()" class="self-start rounded-xl bg-brand-600 px-6 py-3 font-bold text-white">{{ __('app.chapter.next_question') }}</button>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="finished">
                            <div class="flex flex-col items-start gap-4">
                                <x-svg-icon name="check" :size="40" class="text-green-600" />
                                <div class="text-xl font-extrabold" x-text="@js(__('app.chapter.practice_done', ['correct' => '__C__', 'total' => '__T__'])).replace('__C__', correctCount.toLocaleString(@js(app()->getLocale() === 'en' ? 'en' : 'fa'))).replace('__T__', questions.length.toLocaleString(@js(app()->getLocale() === 'en' ? 'en' : 'fa')))"></div>
                                <a href="{{ route('chapters.show', [$course, $chapter, 'test']) }}" class="rounded-xl bg-brand-600 px-6 py-3 font-bold text-white">{{ __('app.chapter.go_test') }}</a>
                            </div>
                        </template>
                    </div>
                @endif
            </div>
        @else
            <div class="mx-auto flex max-w-2xl flex-col gap-4">
                @if ($questions->isEmpty())
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center text-slate-600">{{ __('app.chapter.no_test') }}</div>
                @elseif (! $state['test_unlocked'])
                    <div class="flex flex-col items-start gap-3 rounded-3xl border border-slate-200 bg-white p-6">
                        <x-svg-icon name="lock" :size="36" class="text-slate-400" />
                        <h1 class="text-2xl font-extrabold">{{ __('app.chapter.test') }}</h1>
                        <p class="text-slate-600">{{ __('app.chapter.test_info', ['count' => n($questions->count()), 'mark' => pct(config('zirak.pass_mark'))]) }}</p>
                        <p class="leading-loose text-slate-700">{{ __('app.chapter.test_locked') }}</p>
                    </div>
                @else
                    @if ($lastAttempt)
                        <div @class(['rounded-2xl border p-5', 'border-green-200 bg-green-50 text-green-900' => $lastAttempt->passed, 'border-red-200 bg-red-50 text-red-900' => ! $lastAttempt->passed])>
                            <div class="text-xl font-extrabold">{{ __('app.chapter.score', ['score' => pct($lastAttempt->score)]) }} — {{ $lastAttempt->passed ? __('app.chapter.passed') : '' }}</div>
                            @unless ($lastAttempt->passed)<p class="mt-1">{{ __('app.chapter.failed') }}</p>@endunless
                            @if ($state['best_score'] !== null)<p class="mt-1 text-sm">{{ __('app.chapter.best', ['score' => pct($state['best_score'])]) }}</p>@endif
                            @if ($lastAttempt->passed && $nextChapter)
                                <a href="{{ route('chapters.show', [$course, $nextChapter]) }}" class="mt-3 inline-block rounded-xl bg-brand-600 px-5 py-2.5 font-bold text-white">{{ __('app.chapter.next_chapter') }}</a>
                            @elseif ($lastAttempt->passed)
                                <a href="{{ route('courses.show', $course) }}" class="mt-3 inline-block rounded-xl bg-brand-600 px-5 py-2.5 font-bold text-white">{{ __('app.course.continue') }}</a>
                            @endif
                        </div>
                    @endif
                    <form method="POST" action="{{ route('test.submit', [$course, $chapter]) }}" class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-5 md:p-6">
                        @csrf
                        <div>
                            <h1 class="text-2xl font-extrabold">{{ $lastAttempt ? __('app.chapter.retake') : __('app.chapter.test') }}</h1>
                            <p class="text-sm text-slate-600">{{ __('app.chapter.test_info', ['count' => n($questions->count()), 'mark' => pct(config('zirak.pass_mark'))]) }}</p>
                        </div>
                        @foreach ($questions as $qi => $question)
                            <fieldset class="flex flex-col gap-2 border-t border-slate-100 pt-4">
                                <legend class="mb-2 font-bold leading-loose">{{ n($qi + 1) }}. {{ $question->body }}</legend>
                                @foreach ($question->options as $oi => $option)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-4 py-3 has-checked:border-brand-600 has-checked:bg-brand-50">
                                        <input type="radio" name="answers[{{ $question->id }}]" value="{{ $oi }}" required class="accent-brand-600">
                                        <span>{{ $option }}</span>
                                    </label>
                                @endforeach
                            </fieldset>
                        @endforeach
                        <button class="rounded-xl bg-brand-600 py-3.5 text-lg font-bold text-white">{{ __('app.chapter.submit_test') }}</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</x-layouts.app>
