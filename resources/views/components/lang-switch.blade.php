@props(['compact' => false])
{{-- Language menu: opens below (compact, top bars) or above (sidebar footer). Closes on outside click and Esc. --}}
<div class="relative text-slate-700" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
    <button type="button" @click="open = ! open" :aria-expanded="open.toString()" aria-haspopup="true"
            @class(['flex items-center gap-2 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 focus:outline-none focus-visible:ring-4 focus-visible:ring-brand-100',
                    'px-3 py-2' => $compact, 'w-full px-3 py-2.5' => ! $compact])>
        <x-svg-icon name="globe" :size="18" class="shrink-0 text-slate-500" />
        <span @class(['flex-1 text-start' => ! $compact])>{{ config('zirak.locales.'.app()->getLocale().'.label') }}</span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 text-slate-400 transition" :class="open && 'rotate-180'" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <div x-show="open" x-cloak x-transition.origin.top.duration.150ms role="menu"
         @class(['absolute z-50 w-44 overflow-hidden rounded-2xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10',
                 'end-0 top-full mt-2' => $compact, 'bottom-full start-0 mb-2 w-full' => ! $compact])>
        @foreach (config('zirak.locales') as $code => $locale)
            @php($current = app()->getLocale() === $code)
            <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" role="menuitem" lang="{{ $code === 'prs' ? 'fa-AF' : $code }}" dir="{{ $locale['dir'] }}"
               @if($current) aria-current="true" @endif
               @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] transition', 'bg-brand-50 font-bold text-brand-700' => $current, 'text-slate-700 hover:bg-slate-50' => ! $current])>
                <span class="flex-1">{{ $locale['label'] }}</span>
                @if ($current)<x-svg-icon name="check" :size="16" class="text-brand-600" />@endif
            </a>
        @endforeach
    </div>
</div>
