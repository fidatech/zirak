<x-layouts.app :title="__('app.cert.verify_title')">
    <div class="mx-auto flex max-w-xl flex-col gap-4 px-4 py-8">
        <h1 class="text-2xl font-extrabold">{{ __('app.cert.verify_title') }}</h1>
        <p class="text-slate-600">{{ __('app.cert.verify_text') }}</p>
        <form method="GET" action="{{ route('certificates.verify') }}" class="flex gap-2">
            <input name="code" value="{{ $code }}" placeholder="ZRK-27-XXXXXX" dir="ltr" class="h-12 flex-1 rounded-xl border border-slate-300 bg-white px-4 uppercase" aria-label="{{ __('app.cert.code') }}">
            <button class="rounded-xl bg-brand-600 px-5 font-bold text-white">{{ __('app.cert.verify_btn') }}</button>
        </form>
        @if ($code)
            @if ($certificate)
                <div @class(['rounded-2xl border p-5', 'border-green-200 bg-green-50' => $certificate->isValid(), 'border-red-200 bg-red-50' => ! $certificate->isValid()])>
                    <div @class(['text-lg font-extrabold', 'text-green-800' => $certificate->isValid(), 'text-red-800' => ! $certificate->isValid()])>{{ $certificate->isValid() ? __('app.cert.valid') : __('app.cert.revoked') }}</div>
                    <dl class="mt-3 grid grid-cols-[auto_1fr] gap-x-4 gap-y-1 text-sm">
                        <dt class="text-slate-600">{{ __('app.cert.given_to') }}</dt><dd class="font-bold">{{ $certificate->name_on_certificate }}</dd>
                        <dt class="text-slate-600">{{ __('app.cert.for_course') }}</dt><dd class="font-bold">{{ $certificate->course->title }}</dd>
                        <dt class="text-slate-600">{{ __('app.cert.date') }}</dt><dd>{{ $certificate->issued_at->translatedFormat('j F Y') }}</dd>
                        @if ($certificate->score !== null)<dt class="text-slate-600">{{ __('app.cert.score') }}</dt><dd>{{ pct($certificate->score) }}</dd>@endif
                    </dl>
                    <p class="mt-3 text-xs text-slate-500">{{ __('app.cert.note') }}</p>
                </div>
            @else
                <div class="rounded-2xl border border-slate-200 bg-white p-5 text-slate-700">{{ __('app.cert.not_found') }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>
