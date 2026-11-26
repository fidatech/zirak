<x-layouts.app>
    <div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 py-6 lg:px-8 lg:py-10">
        <section class="flex flex-col items-center gap-6 rounded-3xl bg-brand-600 p-6 text-white md:flex-row md:p-10">
            <div class="flex flex-1 flex-col gap-3">
                <h1 class="text-2xl font-extrabold leading-relaxed md:text-4xl md:leading-snug">{{ __('app.home.guest_title') }}</h1>
                <p class="text-brand-100 md:text-lg">{{ __('app.home.guest_text') }}</p>
                <div class="mt-2 flex flex-wrap gap-3">
                    <a href="{{ route('login') }}" class="rounded-xl bg-white px-6 py-3 font-bold text-slate-900">{{ __('app.home.guest_cta') }}</a>
                    <a href="{{ route('catalog') }}" class="rounded-xl border border-white/50 px-6 py-3">{{ __('app.home.browse') }}</a>
                </div>
            </div>
            <svg class="w-40 shrink-0 md:w-56" viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="56" fill="#115E59"/><rect x="30" y="34" width="60" height="44" rx="10" fill="#CCFBF1"/><circle cx="48" cy="54" r="6" fill="#0F766E"/><circle cx="72" cy="54" r="6" fill="#0F766E"/><path d="M48 66q12 8 24 0" stroke="#0F766E" stroke-width="4" fill="none" stroke-linecap="round"/><path d="M60 16v18M52 20l8-6 8 6" stroke="#FDE68A" stroke-width="4" fill="none" stroke-linecap="round"/><rect x="40" y="82" width="40" height="16" rx="6" fill="#FDE68A"/></svg>
        </section>

        <section>
            <div class="mb-4 flex items-baseline justify-between">
                <h2 class="text-xl font-extrabold">{{ __('app.home.subjects') }}</h2>
                <a href="{{ route('catalog') }}" class="text-sm font-bold text-brand-600">{{ __('app.home.all_subjects') }}</a>
            </div>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3">
                @foreach ($categories as $category)
                    <x-subject-tile :category="$category" />
                @endforeach
            </div>
        </section>

        @if ($featured->isNotEmpty())
        <section>
            <h2 class="mb-4 text-xl font-extrabold">{{ __('app.home.featured') }}</h2>
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:gap-4">
                @foreach ($featured as $course)
                    <x-course-card :course="$course" />
                @endforeach
            </div>
        </section>
        @endif

        <a href="{{ route('certificates.verify') }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4">
            <x-svg-icon name="shield" class="text-brand-600" />
            <span class="font-bold">{{ __('app.cert.verify_title') }}</span>
            <x-svg-icon name="forward" class="ms-auto text-slate-400" :size="18" />
        </a>
    </div>
</x-layouts.app>
