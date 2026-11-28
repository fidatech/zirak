@php($l = fn ($k, $r = []) => __('app.landing.'.$k, $r))
<x-layouts.app landing>
    <x-site-nav />

    {{-- ================= HERO ================= --}}
    <section class="sky relative overflow-hidden pt-28 text-white lg:pt-36">
        {{-- soft clouds (pure CSS) --}}
        <div aria-hidden="true" class="pointer-events-none absolute -start-24 top-24 h-40 w-96 rounded-full bg-white/25 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -end-20 top-52 h-44 w-[28rem] rounded-full bg-white/20 blur-3xl"></div>

        <div class="relative mx-auto flex max-w-5xl flex-col items-center gap-6 px-5 text-center">
            <span class="glass rounded-full px-4 py-1.5 text-sm font-semibold">✦ {{ $l('badge') }}</span>
            <h1 class="text-[2rem] font-black leading-[1.35] tracking-tight drop-shadow-sm sm:text-5xl sm:leading-[1.3] lg:text-6xl lg:leading-[1.25]">
                {{ $l('hero_title') }}
                <span class="mt-2 inline-block rounded-2xl bg-white px-3 py-1 shadow-soft"><span class="text-gradient">{{ $l('hero_highlight') }}</span></span>
            </h1>
            <p class="max-w-2xl text-base leading-8 text-white/90 sm:text-lg">{{ $l('hero_text') }}</p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('catalog') }}" class="rounded-xl bg-white px-6 py-3.5 font-bold text-ink shadow-soft hover:bg-slate-50">{{ __('app.home.browse') }}</a>
                <a href="{{ route('register') }}" class="btn-primary rounded-xl px-7 py-3.5 font-bold">{{ __('app.home.guest_cta') }}</a>
            </div>
            <p class="text-sm text-white/80">{{ $l('hero_note') }}</p>

            {{-- subject chips --}}
            @if ($categories->isNotEmpty())
                <div class="glass mt-4 flex max-w-full gap-2 overflow-x-auto rounded-2xl p-1.5 [scrollbar-width:none]">
                    @foreach ($categories as $i => $category)
                        <a href="{{ route('catalog.category', $category) }}" @class(['shrink-0 rounded-xl px-4 py-2 text-sm font-semibold', 'bg-white text-ink' => $i === 0, 'text-white/90 hover:bg-white/10' => $i > 0])>{{ $category->name() }}</a>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- app preview (HTML, no images) --}}
        <div class="relative mx-auto mt-8 max-w-5xl px-4">
            <div class="overflow-hidden rounded-t-3xl border border-b-0 border-white/40 bg-white text-start text-ink shadow-2xl" dir="{{ text_dir() }}">
                <div class="flex items-center gap-2 border-b border-slate-100 px-4 py-3">
                    <span class="h-3 w-3 rounded-full bg-rose-300"></span><span class="h-3 w-3 rounded-full bg-amber-300"></span><span class="h-3 w-3 rounded-full bg-emerald-300"></span>
                    <span class="ms-3 rounded-lg bg-slate-100 px-3 py-1 text-xs text-slate-500" dir="ltr">zirak.af</span>
                </div>
                <div class="grid gap-4 bg-ground p-4 sm:p-6 md:grid-cols-[1.35fr_1fr]">
                    <div class="flex flex-col gap-4">
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-gradient-to-r from-brand-100 to-pink-100 p-4">
                            <div><div class="text-lg font-black">{{ $l('preview_hello') }}</div><div class="text-sm text-slate-600">{{ $l('preview_sub') }}</div></div>
                            <span class="flex items-center gap-1 rounded-full bg-white px-3 py-1 text-sm font-bold text-orange-500"><x-svg-icon name="flame" :size="16" />{{ n(7) }}</span>
                        </div>
                        <div class="rounded-2xl bg-white p-4 shadow-soft">
                            <div class="text-xs font-bold text-brand-600">{{ $l('preview_next') }}</div>
                            <div class="mt-1 font-black">{{ $l('preview_course') }}</div>
                            <div class="text-sm text-slate-600">{{ $l('preview_chapter') }}</div>
                            <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs font-bold">
                                <span class="rounded-lg bg-emerald-50 py-2 text-emerald-700">✓ {{ $l('learn_title') }}</span>
                                <span class="rounded-lg bg-brand-600 py-2 text-white">{{ $l('practice_title') }}</span>
                                <span class="rounded-lg bg-slate-100 py-2 text-slate-500"><x-svg-icon name="lock" :size="12" class="inline" /> {{ $l('test_title') }}</span>
                            </div>
                            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100"><div class="bg-brand-gradient h-full w-[45%] rounded-full"></div></div>
                        </div>
                        <div class="hidden grid-cols-3 gap-3 sm:grid">
                            @foreach ([[pct(85), 'preview_score', 'text-emerald-600'], [n(12), 'stat_lessons', 'text-brand-600'], [n(200).' KB', 'preview_saved', 'text-accent-500']] as [$v, $k, $c])
                                <div class="rounded-2xl bg-white p-3 shadow-soft"><div @class(['text-xl font-black', $c])>{{ $v }}</div><div class="text-xs text-slate-500">{{ $l($k) }}</div></div>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 rounded-2xl bg-white p-4 shadow-soft">
                        <div class="flex items-center gap-2 font-bold"><span class="bg-brand-gradient flex h-8 w-8 items-center justify-center rounded-full text-white"><x-svg-icon name="chat" :size="16" /></span>{{ $l('preview_ask') }}</div>
                        <div class="self-end rounded-2xl rounded-ee-md bg-brand-600 px-3 py-2 text-sm text-white">{{ $l('preview_q') }}</div>
                        <div class="rounded-2xl rounded-es-md bg-slate-100 px-3 py-2 text-sm leading-7">{{ $l('preview_a') }}</div>
                        <div class="mt-auto flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-400"><span class="flex-1">…</span><x-svg-icon name="send" :size="16" class="text-brand-600" /></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= NUMBERS BAND (hidden until there are courses to count) ================= --}}
    @if ($stats['courses'] > 0)
    <section class="bg-ink text-white">
        <div class="mx-auto grid max-w-6xl grid-cols-2 gap-6 px-5 py-8 text-center md:grid-cols-4">
            @foreach ([[n($stats['courses']), 'stat_courses'], [n($stats['lessons']), 'stat_lessons'], [n($stats['subjects']), 'stat_subjects'], [n(3), 'stat_languages']] as [$value, $label])
                <div><div class="text-gradient text-3xl font-black md:text-4xl">{{ $value }}</div><div class="mt-1 text-sm text-slate-400">{{ $l($label) }}</div></div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- ================= FEATURES with sticky side menu ================= --}}
    @php($sections = ['how' => 'nav_how', 'tutor' => 'nav_tutor', 'offline' => 'nav_offline', 'certificates' => 'nav_cert', 'teach' => 'nav_teach'])
    <div class="rings" x-data="scrollspy(@js(array_keys($sections)))">
        <div class="mx-auto grid max-w-6xl gap-10 px-5 py-16 lg:grid-cols-[230px_1fr] lg:py-24">
            <aside class="hidden lg:block">
                <nav class="sticky top-28 flex flex-col">
                    @foreach ($sections as $id => $key)
                        <a href="#{{ $id }}" class="relative border-b border-slate-200 py-4 text-sm font-bold uppercase tracking-wide transition"
                           :class="active === '{{ $id }}' ? 'text-ink' : 'text-slate-400 hover:text-slate-600'">
                            <span x-show="active === '{{ $id }}'" class="text-brand-600">▸ </span>{{ $l($key) }}
                            <span class="bg-brand-gradient absolute -bottom-px start-0 h-0.5 transition-all" :class="active === '{{ $id }}' ? 'w-full' : 'w-0'"></span>
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div class="flex flex-col gap-24">
                {{-- How it works --}}
                <section id="how" class="scroll-mt-28" data-spy>
                    <h2 class="text-3xl font-black leading-[1.4] tracking-tight sm:text-5xl sm:leading-[1.3]">{{ $l('how_title') }} <span class="marker">{{ $l('how_highlight') }}</span></h2>
                    <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-600">{{ $l('how_text') }}</p>
                    <div class="mt-10 grid gap-6 sm:grid-cols-2">
                        @foreach ([
                            ['learn', 'book', 'from-brand-100 to-white'],
                            ['practice', 'pencil', 'from-pink-100 to-white'],
                            ['test', 'check', 'from-emerald-100 to-white'],
                            ['report', 'chart', 'from-sky-100 to-white'],
                        ] as $i => [$key, $icon, $tint])
                            <div class="flex flex-col gap-3">
                                <div class="flex h-44 items-center justify-center rounded-3xl bg-gradient-to-br {{ $tint }} p-5 shadow-soft">
                                    <div class="w-full max-w-[16rem] rounded-2xl bg-white p-4 shadow-soft">
                                        <div class="flex items-center gap-2 text-sm font-bold"><span class="bg-brand-gradient flex h-8 w-8 items-center justify-center rounded-lg text-white"><x-svg-icon :name="$icon" :size="16" /></span>{{ n($i + 1) }}{{ sep() }}{{ $l($key.'_title') }}</div>
                                        <div class="mt-3 space-y-2"><div class="h-2 w-full rounded bg-slate-100"></div><div class="h-2 w-4/5 rounded bg-slate-100"></div><div class="h-2 w-3/5 rounded bg-brand-200"></div></div>
                                    </div>
                                </div>
                                <h3 class="text-xl font-black">{{ $l($key.'_title') }}</h3>
                                <p class="leading-8 text-slate-600">{{ $l($key.'_text') }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- AI tutor --}}
                <section id="tutor" class="scroll-mt-28" data-spy>
                    <h2 class="text-3xl font-black leading-[1.4] tracking-tight sm:text-5xl sm:leading-[1.3]">{{ $l('tutor_title') }} <span class="marker">{{ $l('tutor_highlight') }}</span></h2>
                    <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-600">{{ $l('tutor_text') }}</p>
                    <div class="mt-10 grid items-stretch gap-6 md:grid-cols-[1.2fr_1fr]">
                        <div class="flex flex-col gap-3 rounded-3xl bg-gradient-to-br from-brand-100 via-white to-sky-100 p-6 shadow-soft">
                            <div class="self-end rounded-2xl rounded-ee-md bg-brand-600 px-4 py-2.5 text-white">{{ $l('preview_q') }}</div>
                            <div class="max-w-[90%] rounded-2xl rounded-es-md bg-white px-4 py-3 leading-8 shadow-soft">{{ $l('preview_a') }}</div>
                            <div class="flex gap-2 text-xs"><span class="rounded-full bg-white px-3 py-1 font-semibold shadow-soft">دری</span><span class="rounded-full bg-white px-3 py-1 font-semibold shadow-soft">پښتو</span><span class="rounded-full bg-white px-3 py-1 font-semibold shadow-soft">English</span></div>
                        </div>
                        <ul class="flex flex-col gap-3">
                            @foreach (['tutor_p1', 'tutor_p2', 'tutor_p3'] as $k)
                                <li class="flex items-start gap-3 rounded-2xl bg-white p-5 shadow-soft"><span class="bg-brand-gradient mt-1 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-white"><x-svg-icon name="check" :size="14" /></span><span class="font-semibold leading-7">{{ $l($k) }}</span></li>
                            @endforeach
                        </ul>
                    </div>
                </section>

                {{-- Offline --}}
                <section id="offline" class="scroll-mt-28" data-spy>
                    <h2 class="text-3xl font-black leading-[1.4] tracking-tight sm:text-5xl sm:leading-[1.3]">{{ $l('offline_title') }} <span class="marker">{{ $l('offline_highlight') }}</span></h2>
                    <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-600">{{ $l('offline_text') }}</p>
                    <div class="mt-10 grid gap-6 sm:grid-cols-3">
                        @foreach ([['offline_p1', 'home'], ['offline_p2', 'download'], ['offline_p3', 'send']] as [$k, $icon])
                            <div class="flex flex-col gap-4 rounded-3xl bg-white p-6 shadow-soft">
                                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600"><x-svg-icon :name="$icon" :size="24" /></span>
                                <span class="font-bold leading-7">{{ $l($k) }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                {{-- Certificates --}}
                <section id="certificates" class="scroll-mt-28" data-spy>
                    <h2 class="text-3xl font-black leading-[1.4] tracking-tight sm:text-5xl sm:leading-[1.3]">{{ $l('cert_title') }} <span class="marker">{{ $l('cert_highlight') }}</span></h2>
                    <p class="mt-4 max-w-2xl text-lg leading-8 text-slate-600">{{ $l('cert_text') }}</p>
                    <div class="mt-10 grid items-center gap-6 md:grid-cols-[1.3fr_1fr]">
                        <div class="rounded-3xl bg-gradient-to-br from-amber-50 via-white to-brand-50 p-3 shadow-soft">
                            <div class="flex flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-amber-300 bg-white px-6 py-8 text-center">
                                <x-svg-icon name="award" :size="40" class="text-amber-500" />
                                <div class="text-sm text-slate-500">{{ __('app.certificate_type.achievement') }}</div>
                                <div class="text-2xl font-black">{{ $l('cert_name') }}</div>
                                <div class="text-slate-600">{{ $l('preview_course') }}</div>
                                <div class="mt-2 rounded-lg bg-slate-100 px-3 py-1 font-mono text-sm" dir="ltr">ZRK-26-7Q4M2X</div>
                            </div>
                        </div>
                        <a href="{{ route('certificates.verify') }}" class="group flex items-center gap-4 rounded-3xl bg-white p-6 shadow-soft">
                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600"><x-svg-icon name="shield" :size="28" /></span>
                            <span class="flex-1 text-lg font-black">{{ $l('cert_verify') }}</span>
                            <x-svg-icon name="forward" class="text-slate-400 transition group-hover:text-brand-600" />
                        </a>
                    </div>
                </section>

                {{-- Teach --}}
                <section id="teach" class="scroll-mt-28" data-spy>
                    <div class="bg-brand-gradient relative overflow-hidden rounded-[2rem] p-8 text-white shadow-soft sm:p-12">
                        <div aria-hidden="true" class="absolute -end-16 -top-16 h-56 w-56 rounded-full bg-white/15 blur-2xl"></div>
                        <h2 class="relative text-3xl font-black leading-[1.4] sm:text-4xl">{{ $l('teach_title') }}<br><span class="rounded-xl bg-white px-2 text-brand-700">{{ $l('teach_highlight') }}</span></h2>
                        <p class="relative mt-4 max-w-xl text-lg leading-8 text-white/90">{{ $l('teach_text', ['share' => pct(config('zirak.teacher_share'))]) }}</p>
                        <a href="{{ auth()->check() ? route('teach.apply') : route('register', ['as' => 'teacher']) }}" class="relative mt-6 inline-block rounded-xl bg-white px-6 py-3.5 font-bold text-ink">{{ $l('teach_cta') }}</a>
                    </div>
                </section>
            </div>
        </div>
    </div>

    {{-- ================= COURSES & SUBJECTS ================= --}}
    <section class="bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-12 px-5 py-16 lg:py-24">
            @if ($featured->isNotEmpty())
                <div>
                    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                        <h2 class="text-3xl font-black leading-[1.4] tracking-tight sm:text-4xl">{{ $l('courses_title') }} <span class="marker">{{ $l('courses_highlight') }}</span></h2>
                        <a href="{{ route('catalog') }}" class="font-bold text-brand-600">{{ __('app.home.browse') }} →</a>
                    </div>
                    <div class="grid grid-cols-2 gap-4 md:grid-cols-3">
                        @foreach ($featured as $course)
                            <x-course-card :course="$course" />
                        @endforeach
                    </div>
                </div>
            @endif
            @if ($categories->isNotEmpty())
                <div>
                    <h2 class="mb-6 text-2xl font-black">{{ $l('subjects_title') }}</h2>
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                        @foreach ($categories as $category)
                            <x-subject-tile :category="$category" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ================= FINAL CTA ================= --}}
    <section class="sky px-5 py-16 text-center text-white">
        <div class="mx-auto flex max-w-3xl flex-col items-center gap-5">
            <h2 class="text-3xl font-black leading-[1.4] sm:text-5xl">{{ $l('final_title') }}</h2>
            <p class="text-lg text-white/90">{{ $l('final_text') }}</p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ route('register') }}" class="rounded-xl bg-white px-7 py-3.5 font-bold text-ink shadow-soft">{{ __('app.home.guest_cta') }}</a>
                @if (app(\App\Services\Telegram\TelegramClient::class)->configured())
                    <a href="{{ route('login.telegram') }}" class="glass rounded-xl px-6 py-3.5 font-bold">{{ __('app.telegram.continue_with') }}</a>
                @endif
            </div>
        </div>
    </section>

    <x-site-footer />
</x-layouts.app>
