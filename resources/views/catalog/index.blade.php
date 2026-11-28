@php($top = 'pt-8 lg:pt-10')
@php($sorts = ['newest' => 'sort_newest', 'popular' => 'sort_popular', 'price_asc' => 'sort_price_low', 'price_desc' => 'sort_price_high'])
@php($pill = 'flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 px-3 py-2 text-sm has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50 has-[:checked]:font-bold has-[:checked]:text-brand-700')
<x-public-page :title="$category?->name() ?? __('app.catalog.title')">
    {{-- Header band --}}
    <section class="sky relative overflow-hidden pb-10 text-white {{ $top }}">
        <div class="relative mx-auto flex max-w-6xl flex-col gap-4 px-5">
            <h1 class="text-3xl font-black leading-[1.4] sm:text-4xl">{{ $category?->name() ?? __('app.catalog.title') }}</h1>
            <p class="max-w-2xl text-white/90">{{ __('app.catalog.intro') }}</p>
            <form method="GET" class="flex max-w-2xl items-center gap-2 rounded-2xl bg-white p-2 shadow-soft">
                <x-svg-icon name="search" :size="20" class="ms-2 shrink-0 text-slate-400" />
                <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('app.courseui.search_hint') }}" class="h-11 w-full bg-transparent text-ink outline-none placeholder:text-slate-400" aria-label="{{ __('app.catalog.search') }}">
                @foreach (request()->except(['q', 'page']) as $key => $value)
                    @foreach ((array) $value as $v)<input type="hidden" name="{{ is_array($value) ? $key.'[]' : $key }}" value="{{ $v }}">@endforeach
                @endforeach
                <button class="btn-primary shrink-0 rounded-xl px-5 py-2.5 font-bold">{{ __('app.catalog.apply') }}</button>
            </form>
            <div class="flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none]">
                <a href="{{ route('catalog') }}" @class(['shrink-0 rounded-xl px-4 py-2 text-sm font-semibold', 'bg-white text-ink' => ! $category, 'glass text-white' => $category])>{{ __('app.all') }}</a>
                @foreach ($categories as $cat)
                    <a href="{{ route('catalog.category', $cat) }}" @class(['shrink-0 rounded-xl px-4 py-2 text-sm font-semibold', 'bg-white text-ink' => $category?->is($cat), 'glass text-white' => ! $category?->is($cat)])>{{ $cat->name() }} <span class="opacity-70">{{ n($cat->courses_count) }}</span></a>
                @endforeach
            </div>
        </div>
    </section>

    <div class="mx-auto flex max-w-6xl flex-col gap-6 px-5 py-8 lg:flex-row lg:items-start">
        {{-- Filters --}}
        <form method="GET" class="shadow-soft rounded-2xl bg-white p-5 ring-1 ring-slate-100 lg:sticky lg:top-24 lg:w-64 lg:shrink-0" x-data="{ open: window.innerWidth >= 1024 || {{ empty(array_diff_key($filters, ['q' => 1, 'sort' => 1])) ? 'false' : 'true' }} }">
            <input type="hidden" name="q" value="{{ $filters['q'] ?? '' }}">
            <input type="hidden" name="sort" value="{{ $filters['sort'] ?? '' }}">
            <button type="button" class="flex w-full items-center justify-between font-black lg:pointer-events-none" @click="open = ! open">
                <span class="flex items-center gap-2"><x-svg-icon name="grid" :size="18" class="text-brand-600" />{{ __('app.catalog.filter') }}</span>
                <span class="text-slate-400 lg:hidden" x-text="open ? '−' : '+'">+</span>
            </button>
            <div class="mt-4 flex-col gap-5" :class="open ? 'flex' : 'hidden'">
                <fieldset class="flex flex-col gap-2"><legend class="mb-2 text-sm font-bold text-slate-500">{{ __('app.catalog.level') }}</legend>
                    @foreach ($levels as $value => $label)
                        <label class="{{ $pill }}"><input type="checkbox" name="level[]" value="{{ $value }}" class="accent-brand-600" @checked(in_array($value, $filters['level'] ?? []))> {{ $label }}</label>
                    @endforeach
                </fieldset>
                <fieldset class="flex flex-col gap-2"><legend class="mb-2 text-sm font-bold text-slate-500">{{ __('app.catalog.language') }}</legend>
                    @foreach (['prs', 'ps', 'en'] as $lang)
                        <label class="{{ $pill }}"><input type="checkbox" name="language[]" value="{{ $lang }}" class="accent-brand-600" @checked(in_array($lang, $filters['language'] ?? []))> {{ __('app.lang.'.$lang) }}</label>
                    @endforeach
                </fieldset>
                <fieldset class="flex flex-col gap-2"><legend class="mb-2 text-sm font-bold text-slate-500">{{ __('app.catalog.price') }}</legend>
                    @foreach (['' => __('app.all'), 'free' => __('app.free'), 'paid' => __('app.paid')] as $value => $label)
                        <label class="{{ $pill }}"><input type="radio" name="price" value="{{ $value }}" class="accent-brand-600" @checked(($filters['price'] ?? '') === $value)> {{ $label }}</label>
                    @endforeach
                </fieldset>
                <button class="btn-primary rounded-xl py-2.5 font-bold">{{ __('app.catalog.apply') }}</button>
                @if (array_diff_key($filters, ['q' => 1]))
                    <a href="{{ url()->current() }}" class="text-center text-sm font-semibold text-slate-500 hover:text-brand-600">{{ __('app.courseui.clear') }}</a>
                @endif
            </div>
        </form>

        {{-- Results --}}
        <div class="min-w-0 flex-1">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="text-sm font-semibold text-slate-600">{{ __('app.catalog.count', ['count' => n($courses->total())]) }}</div>
                <form method="GET" class="flex items-center gap-2 text-sm">
                    @foreach (request()->except(['sort', 'page']) as $key => $value)
                        @foreach ((array) $value as $v)<input type="hidden" name="{{ is_array($value) ? $key.'[]' : $key }}" value="{{ $v }}">@endforeach
                    @endforeach
                    <label for="sort" class="text-slate-500">{{ __('app.courseui.sort') }}</label>
                    <select id="sort" name="sort" onchange="this.form.submit()" class="rounded-xl border border-slate-200 bg-white px-3 py-2 font-semibold">
                        @foreach ($sorts as $value => $key)
                            <option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ __('app.courseui.'.$key) }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            @if ($courses->isEmpty())
                <div class="shadow-soft flex flex-col items-center gap-3 rounded-3xl bg-white p-12 text-center text-slate-600 ring-1 ring-slate-100">
                    <span class="text-5xl" aria-hidden="true">🔎</span>{{ __('app.catalog.empty') }}
                </div>
            @else
                <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:gap-5">
                    @foreach ($courses as $course)
                        <x-course-card :course="$course" />
                    @endforeach
                </div>
                <div class="mt-8">{{ $courses->links() }}</div>
            @endif
        </div>
    </div>
</x-public-page>
