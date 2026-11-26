<x-layouts.app :title="__('app.cert.mine')">
    <div class="mx-auto flex max-w-4xl flex-col gap-4 px-4 py-6 lg:px-8">
        <h1 class="text-2xl font-extrabold">{{ __('app.cert.mine') }}</h1>
        @forelse ($certificates as $certificate)
            <a href="{{ route('certificates.show', $certificate) }}" class="flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4">
                <x-svg-icon name="award" :size="36" class="shrink-0 text-amber-600" />
                <div class="flex-1"><div class="font-bold">{{ $certificate->course->title }}</div><div class="text-sm text-slate-600">{{ $certificate->type->label() }} · {{ $certificate->issued_at->translatedFormat('j F Y') }}</div></div>
                <span class="text-sm text-slate-500" dir="ltr">{{ $certificate->code }}</span>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">{{ __('app.cert.none') }}</div>
        @endforelse
    </div>
</x-layouts.app>
