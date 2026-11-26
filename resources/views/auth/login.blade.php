<x-layouts.app :title="__('app.auth.title')">
    <div class="mx-auto flex max-w-md flex-col gap-5 px-4 py-10">
        <h1 class="text-2xl font-extrabold">{{ __('app.auth.title') }}</h1>
        <form method="POST" action="{{ route('login.send') }}" class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6">
            @csrf
            <label class="flex flex-col gap-1.5 font-bold">{{ __('app.auth.phone') }}
                <input name="phone" type="tel" inputmode="tel" autocomplete="tel" value="{{ old('phone') }}" placeholder="{{ __('app.auth.phone_hint') }}" dir="ltr" required class="h-12 rounded-xl border border-slate-300 px-4 text-lg font-normal">
            </label>
            @error('phone')<div class="text-sm text-red-700">{{ $message }}</div>@enderror
            <button class="rounded-xl bg-brand-600 py-3.5 text-lg font-bold text-white">{{ __('app.auth.send') }}</button>
            <p class="text-sm text-slate-500">{{ __('app.auth.privacy') }}</p>
        </form>
        @if (app(\App\Services\Telegram\TelegramClient::class)->configured())
            <div class="flex items-center gap-3 text-sm text-slate-500"><span class="h-px flex-1 bg-slate-200"></span>{{ __('app.telegram.or') }}<span class="h-px flex-1 bg-slate-200"></span></div>
            <a href="{{ route('login.telegram') }}" class="flex items-center justify-center gap-2 rounded-xl bg-[#229ED9] py-3.5 text-lg font-bold text-white">
                <x-svg-icon name="send" :size="20" />{{ __('app.telegram.continue_with') }}
            </a>
        @endif
    </div>
</x-layouts.app>
