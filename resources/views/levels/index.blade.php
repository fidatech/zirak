<x-public-page :title="__('app.levels.page_title')">
    <section class="bg-brand-600 text-white">
        <div class="mx-auto grid max-w-6xl items-center gap-10 px-5 py-12 lg:grid-cols-[1.2fr_1fr] lg:py-16">
            <div class="flex flex-col gap-4">
                <span class="self-start rounded-lg bg-accent-400 px-3 py-1.5 text-sm font-extrabold text-accent-900">{{ __('app.levels.kicker') }}</span>
                <h1 class="text-3xl font-black leading-tight sm:text-5xl">{{ __('app.levels.title', ['count' => n($levels->count())]) }}</h1>
                <p class="max-w-xl text-lg text-white/90">{{ __('app.levels.text') }}</p>
            </div>
            @include('partials.level-finder', ['selected' => (int) request('from', 1)])
        </div>
    </section>
    <section id="levels" class="scroll-mt-24 bg-brand-50">
        <div class="mx-auto max-w-6xl px-5 py-12">
            @if ($recommended)
                <div class="mb-6 flex flex-wrap items-center gap-3 rounded-2xl bg-white p-4 ring-2 ring-accent-400">
                    <span class="rounded-md bg-accent-400 px-2 py-0.5 text-xs font-extrabold text-accent-900">{{ __('app.levels.recommended') }}</span>
                    <span class="flex-1 font-bold">{{ __('app.levels.level_n', ['n' => n($recommended->number)]) }}{{ sep() }}{{ $recommended->text('title') }}</span>
                    <a href="{{ $recommended->url() }}" class="btn-primary rounded-xl px-5 py-2.5 font-bold">{{ __('app.levels.start_level', ['n' => n($recommended->number)]) }}</a>
                </div>
            @endif
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                @foreach ($levels as $level)
                    <x-level-card :level="$level" :highlight="$recommended?->is($level)" />
                @endforeach
            </div>
        </div>
    </section>
</x-public-page>
