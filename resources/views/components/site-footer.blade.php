<footer class="no-print bg-ink text-slate-300">
    <div class="mx-auto grid max-w-6xl gap-10 px-5 py-14 md:grid-cols-[1.4fr_1fr_1fr_1fr]">
        <div class="flex flex-col gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-white">
                <span class="bg-brand-gradient flex h-10 w-10 items-center justify-center rounded-xl text-lg font-black">{{ mb_substr(__('app.brand'), 0, 1) }}</span>
                <span class="text-2xl font-black">{{ __('app.brand') }}</span>
            </a>
            <p class="max-w-sm text-sm leading-7 text-slate-400">{{ __('app.landing.footer_about') }}</p>
        </div>
        <div class="flex flex-col gap-3 text-sm">
            <div class="font-bold text-white">{{ __('app.landing.footer_learn') }}</div>
            <a href="{{ route('catalog') }}" class="hover:text-white">{{ __('app.nav.courses') }}</a>
            @if (config('zirak.subscription.enabled'))<a href="{{ route('plans') }}" class="hover:text-white">{{ __('app.plan.nav') }}</a>@endif
            <a href="{{ route('home') }}#how" class="hover:text-white">{{ __('app.landing.nav_how') }}</a>
            <a href="{{ route('home') }}#tutor" class="hover:text-white">{{ __('app.landing.nav_tutor') }}</a>
            <a href="{{ route('home') }}#offline" class="hover:text-white">{{ __('app.landing.nav_offline') }}</a>
        </div>
        <div class="flex flex-col gap-3 text-sm">
            <div class="font-bold text-white">{{ __('app.landing.footer_zirak') }}</div>
            <a href="{{ auth()->check() ? route('teach.apply') : route('register', ['as' => 'teacher']) }}" class="hover:text-white">{{ __('app.landing.nav_teach') }}</a>
            <a href="{{ route('certificates.verify') }}" class="hover:text-white">{{ __('app.nav.verify') }}</a>
            <a href="{{ route('login') }}" class="hover:text-white">{{ __('app.nav.login') }}</a>
        </div>
        <div class="flex flex-col gap-3 text-sm">
            <div class="font-bold text-white">{{ __('app.landing.footer_language') }}</div>
            @foreach (config('zirak.locales') as $code => $l)
                <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" @class(['hover:text-white', 'font-bold text-white' => app()->getLocale() === $code])>{{ $l['label'] }}</a>
            @endforeach
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto max-w-6xl px-5 py-5 text-xs text-slate-500">© {{ n(now()->year) }} {{ __('app.brand') }} — zirak.af{{ sep() }}{{ __('app.landing.footer_rights') }}</div>
    </div>
</footer>
