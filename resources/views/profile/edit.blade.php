<x-layouts.app :title="__('app.profile.title')">
    <div class="mx-auto flex max-w-xl flex-col gap-4 px-4 py-6">
        <h1 class="text-2xl font-extrabold">{{ __('app.profile.title') }}</h1>
        <form method="POST" action="{{ route('profile.update') }}" class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6">
            @csrf @method('PUT')
            <label class="flex flex-col gap-1.5 font-bold">{{ __('app.profile.name') }}
                <input name="name" value="{{ old('name', $user->name) }}" class="h-12 rounded-xl border border-slate-300 px-4 font-normal">
            </label>
            <label class="flex flex-col gap-1.5 font-bold">{{ __('app.profile.nickname') }}
                <input name="nickname" value="{{ old('nickname', $user->nickname) }}" class="h-12 rounded-xl border border-slate-300 px-4 font-normal">
            </label>
            <label class="flex flex-col gap-1.5 font-bold">{{ __('app.profile.language') }}
                <select name="locale" class="h-12 rounded-xl border border-slate-300 bg-white px-3 font-normal">
                    @foreach (config('zirak.locales') as $code => $locale)
                        <option value="{{ $code }}" @selected($user->locale === $code)>{{ $locale['label'] }}</option>
                    @endforeach
                </select>
            </label>
            <div class="text-sm text-slate-500" dir="ltr">{{ $user->phone }}</div>
            <button class="rounded-xl bg-brand-600 py-3 font-bold text-white">{{ __('app.save') }}</button>
        </form>
        @if (app(\App\Services\Telegram\TelegramClient::class)->configured())
            <section class="flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-6">
                <div class="flex items-center gap-2 font-extrabold"><x-svg-icon name="send" :size="20" class="text-[#229ED9]" />{{ __('app.telegram.section') }}</div>
                @error('telegram')<div class="rounded-xl bg-red-50 p-3 text-sm text-red-800">{{ $message }}</div>@enderror
                @if ($user->telegram_id)
                    <div class="text-sm text-slate-700">{{ __('app.telegram.connected') }}@if($user->telegram_username) · <bdi dir="ltr">{{ '@'.$user->telegram_username }}</bdi>@endif</div>
                    <form method="POST" action="{{ route('profile.telegram.reminders') }}" class="flex items-center gap-2 text-sm">
                        @csrf @method('PUT')
                        <input type="hidden" name="enabled" value="{{ $user->telegram_reminders ? 0 : 1 }}">
                        <button class="flex items-center gap-2 rounded-xl border border-slate-300 px-3 py-2">
                            <span @class(['flex h-5 w-9 items-center rounded-full p-0.5', 'justify-end bg-brand-600' => $user->telegram_reminders, 'bg-slate-300' => ! $user->telegram_reminders])><span class="h-4 w-4 rounded-full bg-white"></span></span>
                            {{ __('app.telegram.reminders') }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('profile.telegram.unlink') }}">@csrf @method('DELETE')
                        <button class="text-sm text-slate-600 underline">{{ __('app.telegram.disconnect') }}</button>
                    </form>
                @else
                    <p class="text-sm text-slate-600">{{ __('app.telegram.not_connected') }}</p>
                    <a href="{{ route('profile.telegram') }}" class="self-start rounded-xl bg-[#229ED9] px-4 py-2.5 font-bold text-white">{{ __('app.telegram.connect') }}</a>
                @endif
            </section>
        @endif
        <a href="{{ route('certificates.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 font-bold">{{ __('app.cert.mine') }}</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-2xl border border-slate-200 bg-white p-4 text-start text-slate-700">{{ __('app.nav.logout') }}</button></form>
    </div>
</x-layouts.app>
