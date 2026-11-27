<x-auth-shell :title="__('app.auth.code_title')">
    <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-bold text-brand-700">{{ __('app.auth.step', ['n' => n(2)]) }}</span>
    <h1 class="mt-3 text-3xl font-black tracking-tight text-ink">{{ __('app.auth.code_title') }}</h1>
    <p class="mt-2 text-slate-600">{!! __('app.auth.code_text', ['phone' => '<bdi dir="ltr" class="font-bold text-ink">'.e($phone).'</bdi>']) !!}</p>
    <form method="POST" action="{{ route('login.verify') }}" class="mt-6 flex flex-col gap-5">
        @csrf
        <label class="flex flex-col gap-1.5">
            <span class="text-sm font-bold text-slate-700">{{ __('app.auth.code') }}</span>
            <input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="12" required autofocus dir="ltr" placeholder="••••••"
                   class="h-16 rounded-2xl border border-slate-300 bg-white px-4 text-center text-3xl font-bold tracking-[0.6em] outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
            @error('code')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
        </label>
        <button class="btn-primary rounded-2xl py-4 text-lg font-bold">{{ __('app.auth.verify') }}</button>
    </form>
    <div class="mt-5 flex items-center justify-between text-sm">
        <a href="{{ route('login') }}" class="font-bold text-slate-600 hover:text-ink">{{ __('app.auth.change') }}</a>
        <form method="POST" action="{{ route('login.send') }}">@csrf<input type="hidden" name="phone" value="{{ $phone }}"><button class="font-bold text-brand-600">{{ __('app.auth.resend') }}</button></form>
    </div>
</x-auth-shell>
