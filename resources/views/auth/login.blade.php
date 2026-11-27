@php($register = $mode === 'register')
<x-auth-shell :title="$register ? __('app.auth.register_title') : __('app.auth.login_title')">
    <div x-data="{ as: @js($as) }">
        <h1 class="text-3xl font-black tracking-tight text-ink">{{ $register ? __('app.auth.register_title') : __('app.auth.login_title') }}</h1>
        <p class="mt-2 text-slate-600">{{ $register ? __('app.auth.register_text') : __('app.auth.login_text') }}</p>

        <nav class="mt-6 grid grid-cols-2 rounded-2xl bg-slate-100 p-1 text-sm font-bold" aria-label="{{ __('app.auth.title') }}">
            <a href="{{ route('login', array_filter(['as' => $as === 'teacher' ? 'teacher' : null])) }}" @class(['rounded-xl py-2.5 text-center', 'bg-white text-ink shadow-sm' => ! $register, 'text-slate-500' => $register])>{{ __('app.auth.tab_login') }}</a>
            <a href="{{ route('register', array_filter(['as' => $as === 'teacher' ? 'teacher' : null])) }}" @class(['rounded-xl py-2.5 text-center', 'bg-white text-ink shadow-sm' => $register, 'text-slate-500' => ! $register])>{{ __('app.auth.tab_register') }}</a>
        </nav>

        <form method="POST" action="{{ route('login.send') }}" class="mt-6 flex flex-col gap-5">
            @csrf
            <input type="hidden" name="mode" value="{{ $mode }}">
            @if ($register)
                <fieldset>
                    <legend class="mb-2 text-sm font-bold text-slate-700">{{ __('app.auth.i_am') }}</legend>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach (['student' => '🎓', 'teacher' => '🧑‍🏫'] as $role => $emoji)
                            <label class="cursor-pointer rounded-2xl border-2 bg-white p-3.5 transition"
                                   :class="as === '{{ $role }}' ? 'border-brand-500 ring-4 ring-brand-100' : 'border-slate-200 hover:border-slate-300'">
                                <input type="radio" name="as" value="{{ $role }}" x-model="as" class="sr-only">
                                <span class="text-2xl" aria-hidden="true">{{ $emoji }}</span>
                                <span class="mt-1 block font-bold text-ink">{{ __('app.auth.as_'.$role) }}</span>
                                <span class="block text-xs text-slate-500">{{ __('app.auth.as_'.$role.'_text') }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p x-show="as === 'teacher'" x-cloak class="mt-3 rounded-xl bg-brand-50 p-3 text-sm text-brand-800">{{ __('app.auth.teacher_note') }}</p>
                </fieldset>
                <label class="flex flex-col gap-1.5">
                    <span class="text-sm font-bold text-slate-700">{{ __('app.auth.name') }}</span>
                    <input name="name" value="{{ old('name') }}" maxlength="80" required autocomplete="name" placeholder="{{ __('app.auth.name_hint') }}"
                           class="h-13 rounded-2xl border border-slate-300 bg-white px-4 py-3 text-base outline-none transition focus:border-brand-500 focus:ring-4 focus:ring-brand-100">
                    @error('name')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
                </label>
            @else
                <input type="hidden" name="as" value="{{ $as }}">
                @if ($as === 'teacher')<p class="rounded-xl bg-brand-50 p-3 text-sm text-brand-800">{{ __('app.auth.teacher_login_note') }}</p>@endif
            @endif

            <label class="flex flex-col gap-1.5">
                <span class="text-sm font-bold text-slate-700">{{ __('app.auth.phone') }}</span>
                <span class="flex h-13 items-center rounded-2xl border border-slate-300 bg-white transition focus-within:border-brand-500 focus-within:ring-4 focus-within:ring-brand-100" dir="ltr">
                    <span class="flex items-center gap-1.5 border-e border-slate-200 px-3.5 text-sm font-bold text-slate-600">🇦🇫 +93</span>
                    <input name="phone" type="tel" inputmode="tel" autocomplete="tel" value="{{ old('phone') }}" placeholder="{{ __('app.auth.phone_hint') }}" required @unless($register) autofocus @endunless
                           class="h-full min-w-0 flex-1 rounded-e-2xl bg-transparent px-3.5 py-3 text-lg tracking-wide outline-none">
                </span>
                @error('phone')<span class="text-sm text-red-700">{{ $message }}</span>@enderror
            </label>

            <button class="btn-primary flex items-center justify-center gap-2 rounded-2xl py-4 text-lg font-bold">
                {{ $register ? __('app.auth.create') : __('app.auth.send') }}<x-svg-icon name="forward" :size="20" class="rtl:rotate-180" />
            </button>
        </form>

        @if (app(\App\Services\Telegram\TelegramClient::class)->configured())
            <div class="my-5 flex items-center gap-3 text-sm text-slate-500"><span class="h-px flex-1 bg-slate-200"></span>{{ __('app.telegram.or') }}<span class="h-px flex-1 bg-slate-200"></span></div>
            <a href="{{ route('login.telegram') }}" class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white py-3.5 font-bold text-ink shadow-sm hover:bg-slate-50">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[#229ED9] text-white"><x-svg-icon name="send" :size="15" /></span>{{ __('app.telegram.continue_with') }}
            </a>
        @endif

        <p class="mt-6 text-center text-sm text-slate-600">
            @if ($register)
                {{ __('app.auth.have_account') }} <a href="{{ route('login') }}" class="font-bold text-brand-600">{{ __('app.auth.tab_login') }}</a>
            @else
                {{ __('app.auth.no_account') }} <a href="{{ route('register') }}" class="font-bold text-brand-600">{{ __('app.auth.create') }}</a>
            @endif
        </p>
        <p class="mt-4 text-center text-xs leading-relaxed text-slate-500">{{ __('app.auth.terms') }}</p>
    </div>
</x-auth-shell>
