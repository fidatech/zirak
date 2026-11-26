<x-layouts.app :title="__('app.auth.code_title')">
    <div class="mx-auto flex max-w-md flex-col gap-5 px-4 py-10">
        <h1 class="text-2xl font-extrabold">{{ __('app.auth.code_title') }}</h1>
        <form method="POST" action="{{ route('login.verify') }}" class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6">
            @csrf
            <p class="text-slate-700">{!! __('app.auth.code_sent', ['phone' => '<bdi dir="ltr" class="font-bold">'.e($phone).'</bdi>']) !!}</p>
            <label class="flex flex-col gap-1.5 font-bold">{{ __('app.auth.code') }}
                <input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="12" required autofocus dir="ltr" class="h-14 rounded-xl border border-slate-300 px-4 text-center text-2xl tracking-[0.5em]">
            </label>
            @error('code')<div class="text-sm text-red-700">{{ $message }}</div>@enderror
            <button class="rounded-xl bg-brand-600 py-3.5 text-lg font-bold text-white">{{ __('app.auth.verify') }}</button>
        </form>
        <div class="flex justify-between text-sm">
            <a href="{{ route('login') }}" class="font-bold text-brand-600">{{ __('app.auth.change') }}</a>
            <form method="POST" action="{{ route('login.send') }}">@csrf<input type="hidden" name="phone" value="{{ $phone }}"><button class="font-bold text-brand-600">{{ __('app.auth.resend') }}</button></form>
        </div>
    </div>
</x-layouts.app>
