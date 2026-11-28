@props(['solid' => false])
{{-- Floating top bar for the public pages: glass over the hero, white once you scroll (or always, on plain pages). Phones get a menu button. --}}
@php
    $links = array_values(array_filter([
        [route('catalog'), __('app.nav.courses')],
        config('zirak.subscription.enabled') ? [route('plans'), __('app.plan.nav')] : null,
        [route('home').'#how', __('app.landing.nav_how')],
        [route('home').'#tutor', __('app.landing.nav_tutor')],
        [auth()->check() ? route('teach.apply') : route('register', ['as' => 'teacher']), __('app.landing.nav_teach')],
        [route('certificates.verify'), __('app.nav.verify')],
    ]));
@endphp
<header x-data="{ solid: @js($solid), scrolled: @js($solid), menu: false }"
        x-init="scrolled = solid || scrollY > 40" @scroll.window="scrolled = solid || scrollY > 40"
        @keydown.escape.window="menu = false"
        class="no-print fixed inset-x-0 top-0 z-40 px-3 pt-3 lg:px-6">
    <nav :class="{ 'glass-light text-ink shadow-soft': scrolled || menu, 'glass text-white': ! (scrolled || menu) }"
         class="{{ $solid ? 'glass-light text-ink shadow-soft' : 'glass text-white' }} mx-auto max-w-6xl rounded-2xl px-3 py-2.5 transition-colors duration-300 lg:px-5">
        <div class="flex items-center gap-2 lg:gap-6">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2">
                <span class="bg-brand-gradient flex h-9 w-9 items-center justify-center rounded-xl text-lg font-black text-white">{{ mb_substr(__('app.brand'), 0, 1) }}</span>
                <span class="text-xl font-black tracking-tight">{{ __('app.brand') }}</span>
            </a>
            <span class="hidden h-7 w-px bg-current opacity-25 lg:block"></span>
            <div class="hidden items-center gap-1 text-[15px] font-semibold lg:flex">
                @foreach ($links as [$url, $label])
                    <a href="{{ $url }}" class="rounded-lg px-2.5 py-1.5 opacity-90 transition hover:bg-current/10 hover:opacity-100">{{ $label }}</a>
                @endforeach
            </div>
            <div class="ms-auto flex items-center gap-2">
                <x-lang-switch compact />
                @auth
                    <a href="{{ route('home') }}" class="btn-primary hidden rounded-xl px-4 py-2 text-sm font-bold sm:block">{{ __('app.nav.my_courses') }}</a>
                @else
                    <a href="{{ route('login') }}" class="hidden rounded-lg px-2.5 py-1.5 text-sm font-semibold transition hover:bg-current/10 sm:block">{{ __('app.nav.login') }}</a>
                    <a href="{{ route('register') }}" class="btn-primary hidden rounded-xl px-4 py-2 text-sm font-bold sm:block">{{ __('app.home.guest_cta') }}</a>
                @endauth
                <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl transition hover:bg-current/10 lg:hidden"
                        @click="menu = ! menu" :aria-expanded="menu.toString()" aria-controls="site-menu" aria-label="{{ __('app.nav.menu') }}">
                    <svg x-show="! menu" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-show="menu" x-cloak width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
        </div>
        <div id="site-menu" x-show="menu" x-cloak x-transition.opacity.duration.150ms @click.outside="menu = false" class="lg:hidden">
            <div class="mt-3 flex flex-col gap-1 border-t border-slate-200 pt-3 text-[15px] font-semibold text-ink">
                @foreach ($links as [$url, $label])
                    <a href="{{ $url }}" @click="menu = false" class="rounded-xl px-3 py-2.5 hover:bg-slate-100">{{ $label }}</a>
                @endforeach
                <div class="mt-2 grid grid-cols-2 gap-2 border-t border-slate-200 pt-3">
                    @auth
                        <a href="{{ route('home') }}" class="btn-primary col-span-2 rounded-xl py-3 text-center font-bold">{{ __('app.nav.my_courses') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl border border-slate-200 py-3 text-center font-bold">{{ __('app.nav.login') }}</a>
                        <a href="{{ route('register') }}" class="btn-primary rounded-xl py-3 text-center font-bold">{{ __('app.home.guest_cta') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
</header>
