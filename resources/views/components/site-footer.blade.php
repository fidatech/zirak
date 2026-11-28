{{-- Public footer: about + Telegram, the AI levels, learning links, Zirak links, languages; payments and legal line. --}}
@php
    $footerLevels = \App\Models\AiLevel::active()->get();
    $telegram = config('services.telegram.bot_username') ? 'https://t.me/'.config('services.telegram.bot_username') : null;
@endphp
<footer class="no-print bg-brand-900 text-sm text-[#C9C3DC]">
    <div class="mx-auto grid max-w-6xl gap-10 px-5 py-14 sm:grid-cols-2 lg:grid-cols-[1.6fr_1fr_1fr_1fr]">
        <div class="flex flex-col gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 text-white">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-600 text-xl font-black">{{ mb_substr(__('app.brand'), 0, 1) }}</span>
                <span class="text-2xl font-black">{{ __('app.brand') }}</span>
            </a>
            <p class="max-w-sm leading-7">{{ __('app.home2.foot_about') }}</p>
            @if ($telegram)
                <a href="{{ $telegram }}" rel="noopener" class="btn-accent flex items-center gap-2 self-start rounded-lg px-4 py-2.5 font-bold"><x-svg-icon name="send" :size="16" />{{ __('app.home2.join_tg') }}</a>
            @endif
        </div>
        @if ($footerLevels->isNotEmpty())
            <div class="flex flex-col gap-3">
                <div class="font-bold text-white">{{ __('app.levels.nav') }}</div>
                @foreach ($footerLevels as $level)
                    <a href="{{ $level->url() }}" class="hover:text-white">{{ __('app.levels.level_n', ['n' => n($level->number)]) }}{{ sep() }}{{ $level->text('title') }}</a>
                @endforeach
            </div>
        @endif
        <div class="flex flex-col gap-3">
            <div class="font-bold text-white">{{ __('app.home2.foot_learn') }}</div>
            <a href="{{ route('catalog') }}" class="hover:text-white">{{ __('app.nav.courses') }}</a>
            @if (config('zirak.subscription.enabled'))<a href="{{ route('plans') }}" class="hover:text-white">{{ __('app.plan.nav') }}</a>@endif
            <a href="{{ route('levels') }}#level-1" class="hover:text-white">{{ __('app.home2.free_classes') }}</a>
            <a href="{{ route('certificates.verify') }}" class="hover:text-white">{{ __('app.nav.verify') }}</a>
        </div>
        <div class="flex flex-col gap-3">
            <div class="font-bold text-white">{{ __('app.home2.foot_zirak') }}</div>
            <a href="{{ auth()->check() ? route('teach.apply') : route('register', ['as' => 'teacher']) }}" class="hover:text-white">{{ __('app.landing.nav_teach') }}</a>
            @guest<a href="{{ route('login') }}" class="hover:text-white">{{ __('app.nav.login') }}</a>@endguest
            <div class="mt-3 font-bold text-white">{{ __('app.home2.foot_lang') }}</div>
            <div class="flex flex-wrap gap-3">
                @foreach (config('zirak.locales') as $code => $l)
                    <a href="{{ request()->fullUrlWithQuery(['lang' => $code]) }}" @class(['hover:text-white', 'font-bold text-white' => app()->getLocale() === $code])>{{ $l['label'] }}</a>
                @endforeach
            </div>
        </div>
    </div>
    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-2 px-5 py-5 text-xs">
            <span>© {{ n(now()->year) }} {{ __('app.brand') }} — zirak.af</span>
            <span class="flex items-center gap-1.5"><x-svg-icon name="shield" :size="14" />{{ __('app.home2.pay_safe') }}</span>
            <span class="ms-auto">{{ __('app.landing.footer_rights') }}</span>
        </div>
    </div>
</footer>
