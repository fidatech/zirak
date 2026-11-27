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
<aside class="no-print fixed inset-y-0 start-0 z-30 hidden w-60 flex-col gap-1 border-e border-slate-200 bg-white px-4 py-6 lg:flex">
    <a href="{{ route('home') }}" class="mb-5 flex items-center gap-2 px-2">
        <span class="bg-brand-gradient flex h-10 w-10 items-center justify-center rounded-xl text-lg font-extrabold text-white">{{ mb_substr(__('app.brand'), 0, 1) }}</span>
        <span class="text-2xl font-extrabold">{{ __('app.brand') }}</span>
    </a>
    @foreach ($items as [$route, $icon, $label])
        @php($active = request()->routeIs($route) || ($route === 'catalog' && request()->routeIs('catalog.*', 'courses.*', 'chapters.*')))
        <a href="{{ route($route) }}" @class(['flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px]', 'bg-brand-100 font-bold text-brand-600' => $active, 'text-slate-700 hover:bg-slate-50' => ! $active])>
            <x-svg-icon :name="$icon" :size="20" />{{ __('app.'.$label) }}
        </a>
    @endforeach
    @if ($user?->isAdmin())
        <a href="/admin" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] text-slate-700 hover:bg-slate-50"><x-svg-icon name="shield" :size="20" />{{ __('app.nav.admin') }}</a>
    @endif
    <div class="mt-auto flex flex-col gap-2">
        <x-lang-switch />
        @auth
            <form method="POST" action="{{ route('logout') }}" data-logout>@csrf
                <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-[15px] text-slate-600 hover:bg-slate-50"><x-svg-icon name="logout" :size="20" />{{ __('app.nav.logout') }}</button>
            </form>
        @endauth
    </div>
</aside>

<header class="no-print sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:hidden">
    <a href="{{ route('home') }}" class="flex items-center gap-2">
        <span class="bg-brand-gradient flex h-9 w-9 items-center justify-center rounded-xl font-extrabold text-white">{{ mb_substr(__('app.brand'), 0, 1) }}</span>
        <span class="text-xl font-extrabold">{{ __('app.brand') }}</span>
    </a>
    <div class="ms-auto"><x-lang-switch compact /></div>
</header>
@endunless

<main @class(['pb-24 lg:pb-10' => ! $landing, 'pb-16 lg:pb-0' => $landing, 'lg:ps-60' => ! $bare && ! $landing])>
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
<nav class="no-print fixed inset-x-0 bottom-0 z-30 grid border-t border-slate-200 bg-white pb-[env(safe-area-inset-bottom)] lg:hidden" style="grid-template-columns: repeat({{ count($items) }}, minmax(0, 1fr))">
    @foreach ($items as [$route, $icon, $label])
        @php($active = request()->routeIs($route) || ($route === 'catalog' && request()->routeIs('catalog.*', 'courses.*', 'chapters.*')))
        <a href="{{ route($route) }}" @class(['flex flex-col items-center gap-0.5 py-2.5 text-[11.5px]', 'font-bold text-brand-600' => $active, 'text-slate-600' => ! $active])>
            <x-svg-icon :name="$icon" :size="22" />{{ __('app.'.$label) }}
        </a>
    @endforeach
</nav>
@endunless
</body>
</html>
