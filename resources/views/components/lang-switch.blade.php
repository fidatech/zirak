@props(['compact' => false])
<details class="relative">
    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700">
        <x-svg-icon name="globe" :size="18" />{{ config('zirak.locales.'.app()->getLocale().'.label') }}
    </summary>
    <div @class(['absolute z-40 mt-1 w-36 rounded-xl border border-slate-200 bg-white p-1 shadow-lg', 'end-0' => $compact, 'bottom-full mb-1 start-0' => ! $compact])>
        @foreach (config('zirak.locales') as $code => $locale)
            <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" @class(['block rounded-lg px-3 py-2 text-sm hover:bg-slate-50', 'font-bold text-brand-600' => app()->getLocale() === $code]) lang="{{ $code === 'prs' ? 'fa-AF' : $code }}">{{ $locale['label'] }}</a>
        @endforeach
    </div>
</details>
