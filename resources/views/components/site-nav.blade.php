@props(['solid' => false])
{{-- Floating top bar for the public pages: glass over the hero, solid once you scroll (or always, on plain pages). --}}
<header x-data="{ solid: @js($solid), scrolled: @js($solid) }" x-init="scrolled = solid || scrollY > 40" @scroll.window="scrolled = solid || scrollY > 40"
        class="no-print fixed inset-x-0 top-0 z-40 px-3 pt-3 lg:px-6">
    <nav :class="scrolled ? 'glass-light text-ink shadow-soft' : 'glass text-white'"
         class="{{ $solid ? 'glass-light text-ink shadow-soft' : 'glass text-white' }} mx-auto flex max-w-6xl items-center gap-2 rounded-2xl px-3 py-2.5 transition-colors duration-300 lg:gap-6 lg:px-5">
        <a href="{{ route('home') }}" class="flex items-center gap-2">
            <span class="bg-brand-gradient flex h-9 w-9 items-center justify-center rounded-xl text-lg font-black text-white">{{ mb_substr(__('app.brand'), 0, 1) }}</span>
            <span class="text-xl font-black tracking-tight">{{ __('app.brand') }}</span>
        </a>
        <span class="hidden h-7 w-px bg-current opacity-25 lg:block"></span>
        <div class="hidden items-center gap-6 text-[15px] font-semibold lg:flex">
            <a href="{{ route('catalog') }}" class="opacity-90 hover:opacity-100">{{ __('app.nav.courses') }}</a>
            <a href="{{ route('home') }}#how" class="opacity-90 hover:opacity-100">{{ __('app.landing.nav_how') }}</a>
            <a href="{{ route('home') }}#tutor" class="opacity-90 hover:opacity-100">{{ __('app.landing.nav_tutor') }}</a>
            <a href="{{ route('teach.apply') }}" class="opacity-90 hover:opacity-100">{{ __('app.landing.nav_teach') }}</a>
            <a href="{{ route('certificates.verify') }}" class="opacity-90 hover:opacity-100">{{ __('app.nav.verify') }}</a>
        </div>
        <div class="ms-auto flex items-center gap-2">
            <x-lang-switch compact />
            @auth
                <a href="{{ route('home') }}" class="btn-primary rounded-xl px-4 py-2 text-sm font-bold">{{ __('app.nav.my_courses') }}</a>
            @else
                <a href="{{ route('login') }}" class="hidden px-2 text-sm font-semibold sm:block">{{ __('app.nav.login') }}</a>
                <a href="{{ route('login') }}" class="btn-primary rounded-xl px-4 py-2 text-sm font-bold">{{ __('app.home.guest_cta') }}</a>
            @endauth
        </div>
    </nav>
</header>
