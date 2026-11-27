@props(['course'])
@php([$soft, $strong, $dark] = $course->category->palette())
<a href="{{ route('courses.show', $course) }}" class="shadow-soft group flex flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-slate-100 transition hover:-translate-y-1">
    <div class="flex h-32 items-center justify-center" style="background: radial-gradient(circle at 30% 20%, #fff 0, transparent 55%), {{ $soft }}">
        <x-subject-art :category="$course->category" :size="64" />
    </div>
    <div class="flex flex-1 flex-col gap-1 p-4">
        <div class="text-xs font-bold" style="color: {{ $dark }}">{{ $course->category->name() }}{{ sep() }}{{ $course->level->label() }}</div>
        <div class="font-black leading-relaxed group-hover:text-brand-700">{{ $course->title }}</div>
        <div class="text-sm text-slate-600">
            @isset($course->lessons_count){{ n($course->lessons_count) }} {{ __('app.lessons') }}{{ sep() }}@endisset{{ $course->languageLabel() }}
        </div>
        <div class="mt-auto pt-2 text-sm font-bold">
            @if ($course->is_free)
                <span class="text-brand-600">{{ __('app.free') }}</span>
            @else
                {{ afn($course->price_afn) }}
            @endif
        </div>
    </div>
</a>
