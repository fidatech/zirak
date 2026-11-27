@props(['course'])
@php([$soft, $strong, $dark] = $course->category->palette())
<a href="{{ route('courses.show', $course) }}" class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white transition hover:shadow-md">
    <div class="flex h-28 items-center justify-center" style="background: {{ $soft }}">
        <x-subject-art :category="$course->category" :size="64" />
    </div>
    <div class="flex flex-1 flex-col gap-1 p-4">
        <div class="text-xs font-bold" style="color: {{ $dark }}">{{ $course->category->name() }}{{ sep() }}{{ $course->level->label() }}</div>
        <div class="font-bold leading-relaxed">{{ $course->title }}</div>
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
