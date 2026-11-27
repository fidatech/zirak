<x-layouts.app :title="__('app.cert.title')" bare>
    <div class="no-print mx-auto flex max-w-4xl items-center gap-3 px-4 py-4">
        <a href="{{ route('certificates.index') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2">{{ __('app.back') }}</a>
        <button onclick="window.print()" class="rounded-xl bg-brand-600 px-4 py-2 font-bold text-white">{{ __('app.cert.print') }}</button>
    </div>
    <div class="mx-auto max-w-4xl px-4 pb-10">
        <div class="aspect-[1.414] border-[10px] border-brand-600 bg-white p-3 shadow-sm print:shadow-none">
            <div class="flex h-full flex-col items-center justify-center gap-3 border border-brand-300 p-6 text-center">
                <div class="text-lg font-extrabold tracking-wide text-brand-600">{{ __('app.brand') }}</div>
                <div class="text-3xl font-extrabold md:text-4xl">{{ $certificate->type->label() }}</div>
                <div class="mt-4 text-slate-600">{{ __('app.cert.given_to') }}</div>
                <div class="border-b-2 border-slate-200 px-10 pb-1 text-3xl font-bold">{{ $certificate->name_on_certificate }}</div>
                <div class="text-slate-700">{{ __('app.cert.for_course') }} <strong>{{ $certificate->course->title }}</strong></div>
                <div class="text-sm text-slate-600">
                    @if ($certificate->score !== null){{ __('app.cert.score') }}: {{ pct($certificate->score) }}{{ sep() }}@endif{{ __('app.cert.date') }}: {{ $certificate->issued_at->translatedFormat('j F Y') }}
                </div>
                <div class="mt-4 flex flex-col items-center gap-1 text-xs text-slate-500" dir="ltr">
                    <span>{{ __('app.cert.code') }}: <strong>{{ $certificate->code }}</strong></span>
                    <span>{{ $certificate->verifyUrl() }}</span>
                </div>
                <div class="mt-auto text-[11px] text-slate-400">{{ __('app.cert.note') }}</div>
            </div>
        </div>
    </div>
</x-layouts.app>
