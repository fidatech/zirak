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
        <a href="{{ route('certificates.index') }}" class="rounded-2xl border border-slate-200 bg-white p-4 font-bold">{{ __('app.cert.mine') }}</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="w-full rounded-2xl border border-slate-200 bg-white p-4 text-start text-slate-700">{{ __('app.nav.logout') }}</button></form>
    </div>
</x-layouts.app>
