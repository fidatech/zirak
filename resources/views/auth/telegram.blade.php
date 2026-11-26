<x-layouts.app :title="$purpose === 'link' ? __('app.telegram.link_title') : __('app.telegram.login_title')">
    <div class="mx-auto flex max-w-md flex-col gap-5 px-4 py-10"
         x-data="{
            status: 'pending',
            timer: null,
            async poll() {
                try {
                    const res = await fetch(@js($statusUrl), { headers: { 'Accept': 'application/json' } });
                    const data = await res.json();
                    this.status = data.status;
                    if (data.status === 'done') { window.location = data.redirect; return; }
                    if (['expired', 'cancelled', 'used'].includes(data.status)) { clearInterval(this.timer); }
                } catch (e) {}
            },
            init() { this.timer = setInterval(() => this.poll(), 2500); },
         }">
        <h1 class="text-2xl font-extrabold">{{ $purpose === 'link' ? __('app.telegram.link_title') : __('app.telegram.login_title') }}</h1>
        <div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6">
            <ol class="flex list-inside list-decimal flex-col gap-2 text-slate-700">
                <li>{{ __('app.telegram.step1') }}</li>
                <li>{{ __('app.telegram.step2') }}</li>
                <li>{{ __('app.telegram.step3') }}</li>
            </ol>
            <a href="{{ $link }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 rounded-xl bg-[#229ED9] py-3.5 text-lg font-bold text-white">
                <x-svg-icon name="send" :size="20" />{{ __('app.telegram.open') }}
            </a>
            <div class="flex items-center gap-2 text-sm text-slate-600" x-show="status === 'pending' || status === 'confirmed'">
                <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-[#229ED9]"></span>{{ __('app.telegram.waiting') }}
            </div>
            <div class="rounded-xl bg-red-50 p-3 text-sm text-red-800" x-show="['expired', 'cancelled', 'used'].includes(status)" x-cloak>
                {{ __('app.telegram.expired_web') }}
                <a href="{{ request()->url() }}" class="ms-1 font-bold underline">{{ __('app.telegram.retry') }}</a>
            </div>
        </div>
        <a href="{{ $purpose === 'link' ? route('profile') : route('login') }}" class="text-sm font-bold text-brand-600">{{ __('app.back') }}</a>
    </div>
</x-layouts.app>
