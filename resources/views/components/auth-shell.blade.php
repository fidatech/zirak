@props(['title'])
{{-- Split-screen frame for login, sign-up and code pages: form on one side, Zirak story on the other (desktop only). --}}
<x-layouts.app :title="$title" landing bare>
    <div class="flex min-h-dvh bg-ground">
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="flex items-center gap-3 px-4 py-4 sm:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <span class="bg-brand-gradient flex h-9 w-9 items-center justify-center rounded-xl text-lg font-black text-white">{{ mb_substr(__('app.brand'), 0, 1) }}</span>
                    <span class="text-xl font-black tracking-tight text-ink">{{ __('app.brand') }}</span>
                </a>
                <div class="ms-auto"><x-lang-switch compact /></div>
            </header>
            <main class="flex flex-1 items-start justify-center px-4 pb-12 pt-2 sm:items-center sm:px-8">
                <div class="w-full max-w-md">{{ $slot }}</div>
            </main>
            <p class="flex items-center justify-center gap-1.5 pb-6 text-xs text-slate-500">
                <x-svg-icon name="shield" :size="14" />{{ __('app.auth.secure') }}
            </p>
        </div>

        <aside class="sky relative hidden w-[44%] max-w-2xl flex-col justify-between overflow-hidden p-12 text-white lg:flex" aria-hidden="true">
            <div class="pointer-events-none absolute -end-24 -top-24 h-80 w-80 rounded-full bg-white/10 blur-2xl"></div>
            <div class="relative">
                <span class="glass inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-sm font-semibold"><x-svg-icon name="sparkle" :size="16" />{{ __('app.landing.badge') }}</span>
                <h2 class="mt-6 max-w-md text-4xl font-black leading-tight tracking-tight">{{ __('app.auth.side_title') }}</h2>
                <ul class="mt-8 flex flex-col gap-3 text-[15px]">
                    @foreach (['side_1' => 'globe', 'side_2' => 'chat', 'side_3' => 'cloud-off', 'side_4' => 'award'] as $key => $icon)
                        <li class="flex items-center gap-3"><span class="glass flex h-9 w-9 shrink-0 items-center justify-center rounded-xl"><x-svg-icon :name="$icon" :size="18" /></span>{{ __('app.auth.'.$key) }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="glass relative mt-10 rounded-3xl p-6">
                <p class="text-sm font-semibold text-white/80">{{ __('app.auth.side_path') }}</p>
                <ol class="mt-4 grid grid-cols-4 gap-2 text-center text-sm font-bold">
                    @foreach (['📖' => 'path_learn', '✍️' => 'path_practice', '✅' => 'path_test', '🏅' => 'path_certificate'] as $emoji => $key)
                        <li class="flex flex-col items-center gap-2 rounded-2xl bg-white/15 px-2 py-3"><span class="text-2xl">{{ $emoji }}</span>{{ __('app.auth.'.$key) }}</li>
                    @endforeach
                </ol>
            </div>
        </aside>
    </div>
</x-layouts.app>
