<x-layouts.app :title="$category?->name() ?? __('app.catalog.title')">
    <div class="mx-auto max-w-6xl px-4 py-6 lg:px-8 lg:py-8">
        <h1 class="text-2xl font-extrabold md:text-3xl">{{ $category?->name() ?? __('app.catalog.title') }}</h1>
        <p class="mt-1 text-slate-600">{{ __('app.catalog.intro') }}</p>

        <div class="mt-5 flex gap-2 overflow-x-auto pb-2">
            <a href="{{ route('catalog') }}" @class(['shrink-0 rounded-full px-4 py-2 text-sm', 'bg-slate-900 text-white' => ! $category, 'border border-slate-200 bg-white text-slate-700' => $category])>{{ __('app.all') }}</a>
            @foreach ($categories as $cat)
                <a href="{{ route('catalog.category', $cat) }}" @class(['shrink-0 rounded-full px-4 py-2 text-sm', 'bg-slate-900 text-white' => $category?->is($cat), 'border border-slate-200 bg-white text-slate-700' => ! $category?->is($cat)])>{{ $cat->name() }} <span class="opacity-70">{{ n($cat->courses_count) }}</span></a>
            @endforeach
        </div>

        <div class="mt-4 flex flex-col gap-5 lg:flex-row lg:items-start">
            <form method="GET" class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-4 lg:w-60 lg:shrink-0">
                <label class="flex items-center gap-2 rounded-xl border border-slate-200 px-3">
                    <x-svg-icon name="search" :size="18" class="text-slate-500" />
                    <span class="sr-only">{{ __('app.catalog.search') }}</span>
                    <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('app.catalog.search') }}" class="h-11 w-full bg-transparent outline-none">
                </label>
                <div x-data="{ open: {{ empty($filters) ? 'false' : 'true' }} }">
                    <button type="button" class="font-bold lg:hidden" @click="open = ! open">{{ __('app.catalog.filter') }}</button>
                    <div class="mt-3 flex-col gap-4 text-sm lg:mt-0 lg:flex" :class="open ? 'flex' : 'hidden'">
                        <fieldset class="flex flex-col gap-1.5"><legend class="mb-1 font-bold">{{ __('app.catalog.level') }}</legend>
                            @foreach ($levels as $value => $label)
                                <label class="flex items-center gap-2"><input type="checkbox" name="level[]" value="{{ $value }}" @checked(in_array($value, $filters['level'] ?? []))> {{ $label }}</label>
                            @endforeach
                        </fieldset>
                        <fieldset class="flex flex-col gap-1.5"><legend class="mb-1 font-bold">{{ __('app.catalog.language') }}</legend>
                            @foreach (['prs', 'ps', 'en'] as $lang)
                                <label class="flex items-center gap-2"><input type="checkbox" name="language[]" value="{{ $lang }}" @checked(in_array($lang, $filters['language'] ?? []))> {{ __('app.lang.'.$lang) }}</label>
                            @endforeach
                        </fieldset>
                        <fieldset class="flex flex-col gap-1.5"><legend class="mb-1 font-bold">{{ __('app.catalog.price') }}</legend>
                            <label class="flex items-center gap-2"><input type="radio" name="price" value="" @checked(empty($filters['price']))> {{ __('app.all') }}</label>
                            <label class="flex items-center gap-2"><input type="radio" name="price" value="free" @checked(($filters['price'] ?? '') === 'free')> {{ __('app.free') }}</label>
                            <label class="flex items-center gap-2"><input type="radio" name="price" value="paid" @checked(($filters['price'] ?? '') === 'paid')> {{ __('app.paid') }}</label>
                        </fieldset>
                    </div>
                </div>
                <button class="rounded-xl bg-brand-600 py-2.5 font-bold text-white">{{ __('app.catalog.apply') }}</button>
            </form>

            <div class="min-w-0 flex-1">
                <div class="mb-3 text-sm text-slate-600">{{ __('app.catalog.count', ['count' => n($courses->total())]) }}</div>
                @if ($courses->isEmpty())
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">{{ __('app.catalog.empty') }}</div>
                @else
                    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:gap-4">
                        @foreach ($courses as $course)
                            <x-course-card :course="$course" />
                        @endforeach
                    </div>
                    <div class="mt-6">{{ $courses->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
