@props(['solid' => true])
{{-- Public top bar: an announcement strip, then a white header that stays at the top. Phones get a menu button. --}}
@php
    $links = array_values(array_filter([
        [route('levels'), __('app.levels.nav')],
        [route('catalog'), __('app.home2.courses_menu')],
        config('zirak.subscription.enabled') ? [route('plans'), __('app.plan.nav')] : null,
        [auth()->check() ? route('teach.apply') : route('register', ['as' => 'teacher']), __('app.home2.teachers_menu')],
        [route('certificates.verify'), __('app.nav.verify'), 'xl'],
    ]));
    $telegram = config('services.telegram.bot_username') ? 'https://t.me/'.config('services.telegram.bot_username') : null;
@endphp
@if (config('zirak.subscription.enabled'))
    <div class="no-print bg-brand-800 text-sm text-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-4 gap-y-1 px-4 py-2 lg:px-6">
            <span class="rounded bg-accent-400 px-2 py-0.5 text-xs font-extrabold text-accent-900">{{ __('app.home2.new') }}</span>
            <span class="min-w-0 flex-1">{{ __('app.home2.strip', ['price' => afn(config('zirak.subscription.price_afn')), 'days' => n(config('zirak.subscription.days'))]) }}
                <a href="{{ route('plans') }}" class="font-bold text-white underline underline-offset-2">{{ __('app.home2.strip_link') }}</a></span>
            @if ($telegram)<a href="{{ $telegram }}" rel="noopener" class="hidden items-center gap-1.5 text-white sm:flex"><x-svg-icon name="send" :size="15" />{{ __('app.home2.help_tg') }}</a>@endif
        </div>
    </div>
@endif
<header x-data="{ menu: false }" @keydown.escape.window="menu = false" class="no-print sticky top-0 z-40 border-b border-line bg-white text-ink">
    <div class="mx-auto flex max-w-6xl items-center gap-2 px-4 py-3 lg:px-6">
        <a href="{{ route('home') }}" class="me-2 flex shrink-0 items-center gap-2.5">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-xl font-black text-white">{{ mb_substr(__('app.brand'), 0, 1) }}</span>
            <span class="flex flex-col leading-tight"><span class="text-xl font-black">{{ __('app.brand') }}</span><span class="hidden text-[11px] font-semibold text-muted sm:block lg:hidden xl:block">{{ __('app.home2.tagline') }}</span></span>
        </a>
        <nav class="hidden items-center gap-0.5 text-[15px] font-semibold lg:flex" aria-label="{{ __('app.nav.menu') }}">
            @foreach ($links as $link)
                <a href="{{ $link[0] }}" @class(['whitespace-nowrap rounded-lg px-2.5 py-2 transition hover:bg-brand-50 hover:text-brand-700', 'hidden xl:block' => ($link[2] ?? null) === 'xl', 'text-brand-700' => url()->current() === strtok($link[0], '#')])>{{ $link[1] }}</a>
            @endforeach
        </nav>
        <div class="ms-auto flex items-center gap-2">
            <x-lang-switch compact />
            @auth
                <a href="{{ route('home') }}" class="btn-primary hidden rounded-xl px-4 py-2.5 text-sm font-bold sm:block">{{ __('app.nav.my_courses') }}</a>
            @else
                <a href="{{ route('login') }}" class="hidden whitespace-nowrap rounded-lg px-3 py-2 text-sm font-bold transition hover:bg-brand-50 sm:block">{{ __('app.nav.login') }}</a>
                <a href="{{ route('levels') }}#level-1" class="btn-primary hidden whitespace-nowrap rounded-xl px-4 py-2.5 text-sm font-bold sm:block">{{ __('app.home2.try_free') }}</a>
            @endauth
            <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl transition hover:bg-brand-50 lg:hidden"
                    @click="menu = ! menu" :aria-expanded="menu.toString()" aria-controls="site-menu" aria-label="{{ __('app.nav.menu') }}">
                <svg x-show="! menu" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg x-show="menu" x-cloak width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
    </div>
    <div id="site-menu" x-show="menu" x-cloak @click.outside="menu = false" class="border-t border-line bg-white lg:hidden">
        <div class="mx-auto flex max-w-6xl flex-col gap-1 px-4 py-3 text-[15px] font-semibold">
            @foreach ($links as [$url, $label])
                <a href="{{ $url }}" @click="menu = false" class="rounded-xl px-3 py-2.5 hover:bg-brand-50">{{ $label }}</a>
            @endforeach
            <div class="mt-2 grid grid-cols-2 gap-2 border-t border-line pt-3">
                @auth
                    <a href="{{ route('home') }}" class="btn-primary col-span-2 rounded-xl py-3 text-center font-bold">{{ __('app.nav.my_courses') }}</a>
                @else
                    <a href="{{ route('login') }}" class="btn-outline rounded-xl py-3 text-center font-bold">{{ __('app.nav.login') }}</a>
                    <a href="{{ route('levels') }}#level-1" class="btn-primary rounded-xl py-3 text-center font-bold">{{ __('app.home2.try_free') }}</a>
                @endauth
            </div>
        </div>
    </div>
</header>
