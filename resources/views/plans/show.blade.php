@php($top = 'pt-8 lg:pt-10')
<x-public-page :title="__('app.plan.name')">
    <section class="sky relative overflow-hidden px-4 pb-24 text-white {{ $top }}">
        <div class="mx-auto max-w-3xl text-center">
            <span class="glass inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-semibold"><x-svg-icon name="sparkle" :size="16" />{{ __('app.plan.hero_kicker') }}</span>
            <h1 class="mt-5 text-3xl font-black leading-tight tracking-tight sm:text-5xl">{{ __('app.plan.hero_title') }}</h1>
            <p class="mx-auto mt-4 max-w-xl text-white/85 sm:text-lg">{{ __('app.plan.hero_text', ['count' => n($courseCount)]) }}</p>
        </div>
    </section>

    <div class="relative z-10 mx-auto -mt-16 grid max-w-5xl items-start gap-5 px-4 pb-10 lg:grid-cols-[1.25fr_1fr]">
        <div class="shadow-soft relative rounded-3xl bg-white p-6 ring-2 ring-brand-500 sm:p-8">
            <span class="bg-brand-gradient absolute -top-3 start-6 rounded-full px-3 py-1 text-xs font-bold text-white">{{ __('app.plan.name') }}</span>
            <div class="flex items-end gap-2">
                <span class="text-4xl font-black tracking-tight sm:text-5xl">{{ afn($price) }}</span>
                <span class="pb-1.5 text-slate-500">{{ __('app.plan.per_period', ['days' => n($days)]) }}</span>
            </div>
            @if ($paidValue > $price)
                <p class="mt-2 text-sm text-slate-600">{{ __('app.plan.value', ['price' => afn($paidValue)]) }}</p>
            @endif
            <ul class="mt-6 flex flex-col gap-3 text-[15px] text-slate-700">
                @foreach (['inc_all' => ['count' => n($courseCount)], 'inc_practice' => [], 'inc_tutor' => [], 'inc_cert' => [], 'inc_offline' => []] as $key => $args)
                    <li class="flex items-start gap-3"><span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600"><x-svg-icon name="check" :size="14" /></span>{{ __('app.plan.'.$key, $args) }}</li>
                @endforeach
            </ul>
            @if ($endsAt)
                <div class="mt-6 rounded-2xl bg-green-50 p-4 text-sm font-bold text-green-900">{{ __('app.plan.active_until', ['date' => n($endsAt->translatedFormat('j F Y'))]) }}</div>
            @endif
            @auth
                <form method="POST" action="{{ route('plans.subscribe') }}" class="mt-6">@csrf
                    <button class="btn-primary w-full rounded-2xl py-4 text-lg font-bold">{{ $endsAt ? __('app.plan.extend', ['days' => n($days)]) : __('app.plan.subscribe') }}</button>
                </form>
            @else
                <a href="{{ route('register', ['next' => '/plans']) }}" class="btn-primary mt-6 block rounded-2xl py-4 text-center text-lg font-bold">{{ __('app.plan.login_to_subscribe') }}</a>
            @endauth
        </div>

        <div class="flex flex-col gap-5">
            <div class="rounded-3xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-black">{{ __('app.plan.single_title') }}</h2>
                <p class="mt-2 text-sm text-slate-600">{{ __('app.plan.single_text') }}</p>
                <a href="{{ route('catalog') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold hover:bg-slate-50">{{ __('app.plan.browse') }}<x-svg-icon name="forward" :size="16" class="rtl:rotate-180" /></a>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-black">{{ __('app.plan.faq_title') }}</h2>
                @foreach ([1, 2, 3] as $q)
                    <details @class(['group py-3', 'border-t border-slate-100' => $q > 1])>
                        <summary class="flex cursor-pointer list-none items-center gap-2 font-bold">
                            <span class="flex-1">{{ __('app.plan.faq'.$q.'_q') }}</span>
                            <span class="text-slate-400 transition group-open:rotate-90 rtl:-scale-x-100"><x-svg-icon name="forward" :size="16" /></span>
                        </summary>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ __('app.plan.faq'.$q.'_a', ['days' => n($days)]) }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </div>

    @if ($courses->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pb-16">
            <h2 class="mb-4 text-xl font-black">{{ __('app.plan.inc_all', ['count' => n($courseCount)]) }}</h2>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($courses as $course)<x-course-card :course="$course" />@endforeach
            </div>
        </section>
    @endif
</x-public-page>
