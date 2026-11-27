@props(['title' => null, 'bare' => false, 'landing' => false])
<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'prs' ? 'fa-AF' : app()->getLocale() }}" dir="{{ text_dir() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2E2A93">
    <title>{{ $title ? $title.sep() : '' }}{{ __('app.brand') }}</title>
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/icons/icon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ground font-sans text-slate-900 antialiased">
@php
    $user = auth()->user();
    $items = $user
        ? [['home', 'home', 'nav.home'], ['catalog', 'grid', 'nav.courses'], ['report', 'chart', 'nav.report'], ['certificates.index', 'award', 'nav.certificates'], ['profile', 'user', 'nav.profile']]
        : [['home', 'home', 'nav.home'], ['catalog', 'grid', 'nav.courses'], ['certificates.verify', 'shield', 'nav.verify'], ['login', 'user', 'nav.login']];
@endphp

@unless ($bare || $landing)
@php
    $isActive = fn (string $route) => request()->routeIs($route) || ($route === 'catalog' && request()->routeIs('catalog.*', 'courses.*', 'chapters.*'));
    $groups = $user
        ? [
            'app.nav.group_learn' => [['home', 'home', 'nav.home'], ['catalog', 'grid', 'nav.courses'], ['report', 'chart', 'nav.report'], ['certificates.index', 'award', 'nav.certificates']],
            'app.nav.group_account' => [['profile', 'user', 'nav.profile']],
        ]
        : ['app.nav.group_learn' => [['home', 'home', 'nav.home'], ['catalog', 'grid', 'nav.courses'], ['certificates.verify', 'shield', 'nav.verify'], ['login', 'user', 'nav.login']]];
@endphp
<aside class="no-print fixed inset-y-0 start-0 z-30 hidden w-64 flex-col border-e border-slate-200/70 bg-gradient-to-b from-white via-white to-brand-50/60 px-4 pb-5 pt-5 lg:flex">
    <a href="{{ route('home') }}" class="mb-6 flex items-center gap-2.5 rounded-2xl px-2 py-1">
        <span class="bg-brand-gradient flex h-10 w-10 items-center justify-center rounded-xl text-lg font-black text-white shadow-soft">{{ mb_substr(__('app.brand'), 0, 1) }}</span>
        <span class="flex flex-col leading-tight"><span class="text-xl font-black">{{ __('app.brand') }}</span><span class="text-[11px] text-slate-500">{{ __('app.landing.badge') }}</span></span>
    </a>
    <nav class="flex flex-1 flex-col gap-5 overflow-y-auto">
        @foreach ($groups as $groupLabel => $links)
            <div class="flex flex-col gap-1">
                <div class="px-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __($groupLabel) }}</div>
                @foreach ($links as [$route, $icon, $label])
                    @php($active = $isActive($route))
                    <a href="{{ route($route) }}" @class(['relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] transition',
                        'bg-white font-bold text-brand-700 shadow-soft ring-1 ring-slate-100' => $active,
                        'text-slate-600 hover:bg-white/70 hover:text-slate-900' => ! $active])>
                        @if ($active)<span class="bg-brand-gradient absolute inset-y-2 start-0 w-1 rounded-full"></span>@endif
                        <x-svg-icon :name="$icon" :size="20" />{{ __('app.'.$label) }}
                    </a>
                @endforeach
            </div>
        @endforeach
        @if ($user)
            <div class="flex flex-col gap-1">
                <div class="px-3 pb-1 text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ __('app.nav.group_more') }}</div>
                @if ($user->isTeacher())
                    <a href="/teach" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] text-slate-600 hover:bg-white/70"><x-svg-icon name="pencil" :size="20" />{{ __('app.teach.open_area') }}</a>
                @else
                    <a href="{{ route('teach.apply') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] text-slate-600 hover:bg-white/70"><x-svg-icon name="pencil" :size="20" />{{ __('app.teach.cta') }}</a>
                @endif
                @if ($user->isAdmin())
                    <a href="/admin" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] text-slate-600 hover:bg-white/70"><x-svg-icon name="shield" :size="20" />{{ __('app.nav.admin') }}</a>
                @endif
            </div>
        @endif
    </nav>
    <div class="mt-4 flex flex-col gap-2 border-t border-slate-200/70 pt-4">
        <x-lang-switch />
        @auth
            <form method="POST" action="{{ route('logout') }}" data-logout>@csrf
                <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] text-slate-600 hover:bg-white/70"><x-svg-icon name="logout" :size="20" />{{ __('app.nav.logout') }}</button>
            </form>
        @endauth
    </div>
</aside>

{{-- Desktop top bar: search, date, streak, avatar --}}
<div class="no-print sticky top-0 z-20 hidden border-b border-slate-200/70 bg-white/85 backdrop-blur lg:block lg:ps-64">
    <div class="flex items-center gap-3 px-8 py-3">
        <form action="{{ route('catalog') }}" class="flex w-full max-w-md items-center gap-2 rounded-xl bg-slate-100/80 px-3 py-2 text-slate-500 focus-within:ring-2 focus-within:ring-brand-300">
            <x-svg-icon name="search" :size="18" />
            <input name="q" value="{{ request('q') }}" placeholder="{{ __('app.catalog.search') }}" class="w-full bg-transparent text-sm text-slate-900 outline-none placeholder:text-slate-500">
        </form>
        <div class="ms-auto flex items-center gap-2">
            <span class="hidden items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-600 xl:flex"><x-svg-icon name="calendar" :size="16" />{{ n(now()->translatedFormat('j F Y')) }}</span>
            @auth
                <a href="{{ route('report') }}" class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-orange-500" title="{{ __('app.dash.streak') }}"><x-svg-icon name="flame" :size="16" />{{ n(app(\App\Services\ProgressService::class)->streak($user)) }}</a>
                <a href="{{ route('profile') }}" class="flex items-center gap-2 rounded-xl px-2 py-1 hover:bg-slate-50">
                    <span class="bg-brand-gradient flex h-9 w-9 items-center justify-center rounded-full font-black text-white">{{ mb_substr($user->displayName(), 0, 1) }}</span>
                    <span class="hidden text-sm font-semibold xl:block">{{ $user->displayName() }}</span>
                </a>
            @else
                <a href="{{ route('register') }}" class="btn-primary rounded-xl px-4 py-2 text-sm font-bold">{{ __('app.home.guest_cta') }}</a>
            @endauth
        </div>
    </div>
</div>

<header class="no-print sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200/70 bg-white/90 px-4 py-3 backdrop-blur lg:hidden">
    <a href="{{ route('home') }}" class="flex items-center gap-2">
        <span class="bg-brand-gradient flex h-9 w-9 items-center justify-center rounded-xl font-black text-white">{{ mb_substr(__('app.brand'), 0, 1) }}</span>
        <span class="text-xl font-black">{{ __('app.brand') }}</span>
    </a>
    <div class="ms-auto flex items-center gap-2">
        @auth<a href="{{ route('report') }}" class="flex items-center gap-1 rounded-xl border border-slate-200 px-2.5 py-1.5 text-sm font-bold text-orange-500"><x-svg-icon name="flame" :size="16" />{{ n(app(\App\Services\ProgressService::class)->streak($user)) }}</a>@endauth
        <x-lang-switch compact />
    </div>
</header>
@endunless

<main @class(['pb-24 lg:pb-10' => ! $landing, 'pb-16 lg:pb-0' => $landing, 'lg:ps-64' => ! $bare && ! $landing])>
    <div x-data="connection" x-show="! online" x-cloak role="status" class="flex items-center gap-2 bg-slate-800 px-4 py-2 text-sm text-white">
        <x-svg-icon name="cloud-off" :size="16" />{{ __('app.offline.bar') }}
    </div>
    @if (session('status'))
        <div class="no-print mx-auto mt-4 max-w-5xl px-4">
            <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-green-900" role="status">{{ session('status') }}</div>
        </div>
    @endif
    {{ $slot }}
</main>

@unless ($bare)
<nav class="no-print fixed inset-x-0 bottom-0 z-30 grid border-t border-slate-200/70 bg-white/95 pb-[env(safe-area-inset-bottom)] backdrop-blur lg:hidden" style="grid-template-columns: repeat({{ count($items) }}, minmax(0, 1fr))">
    @foreach ($items as [$route, $icon, $label])
        @php($active = request()->routeIs($route) || ($route === 'catalog' && request()->routeIs('catalog.*', 'courses.*', 'chapters.*')))
        <a href="{{ route($route) }}" @class(['flex flex-col items-center gap-0.5 py-2 text-[11.5px]', 'font-bold text-brand-600' => $active, 'text-slate-500' => ! $active])>
            <span @class(['flex h-8 w-12 items-center justify-center rounded-full', 'bg-brand-100' => $active])><x-svg-icon :name="$icon" :size="22" /></span>{{ __('app.'.$label) }}
        </a>
    @endforeach
</nav>
@endunless
</body>
</html>
