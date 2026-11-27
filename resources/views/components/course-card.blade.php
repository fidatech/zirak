@props(['course'])
@php([$soft, $strong, $dark] = $course->category->palette())
@php($minutes = isset($course->lessons_sum_duration_seconds) ? (int) ceil($course->lessons_sum_duration_seconds / 60) : null)
<a href="{{ route('courses.show', $course) }}" class="shadow-soft group flex flex-col overflow-hidden rounded-3xl bg-white ring-1 ring-slate-100 transition hover:-translate-y-1">
    <div class="relative flex h-32 items-center justify-center sm:h-36" style="background: radial-gradient(circle at 30% 20%, #fff 0, transparent 55%), {{ $soft }}">
        <x-subject-art :category="$course->category" :size="64" />
        @if ($course->is_featured)
            <span class="absolute start-3 top-3 rounded-full bg-amber-300 px-2.5 py-0.5 text-[11px] font-black text-amber-950">★ {{ __('app.courseui.pick') }}</span>
        @endif
        <span class="absolute bottom-3 end-3 rounded-full bg-white/90 px-2.5 py-0.5 text-[11px] font-bold text-slate-700">{{ $course->languageLabel() }}</span>
    </div>
    <div class="flex flex-1 flex-col gap-1.5 p-4">
        <div class="text-xs font-bold" style="color: {{ $dark }}">{{ $course->category->name() }}{{ sep() }}{{ $course->level->label() }}</div>
        <div class="line-clamp-2 font-black leading-relaxed group-hover:text-brand-700">{{ $course->title }}</div>
        @if ($course->relationLoaded('teacher') && $course->teacher)
            <div class="truncate text-xs text-slate-500">{{ __('app.courseui.by', ['name' => $course->teacher->publicName()]) }}</div>
        @elseif ($course->instructor_name)
            <div class="truncate text-xs text-slate-500">{{ __('app.courseui.by', ['name' => $course->instructor_name]) }}</div>
        @endif
        <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500">
            @isset($course->lessons_count)<span class="flex items-center gap-1"><x-svg-icon name="book" :size="13" />{{ n($course->lessons_count) }} {{ __('app.lessons') }}</span>@endisset
            @if ($minutes)<span class="flex items-center gap-1"><x-svg-icon name="play" :size="13" />{{ duration($minutes) }}</span>@endif
            @if (! empty($course->enrollments_count))<span class="flex items-center gap-1"><x-svg-icon name="user" :size="13" />{{ __('app.courseui.students', ['n' => n($course->enrollments_count)]) }}</span>@endif
        </div>
        <div class="mt-auto flex items-center justify-between pt-2">
            @if ($course->is_free)
                <span class="rounded-full bg-emerald-50 px-2.5 py-0.5 text-sm font-black text-emerald-700">{{ __('app.free') }}</span>
            @else
                <span class="text-base font-black">{{ afn($course->price_afn) }}</span>
            @endif
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white"><x-svg-icon name="forward" :size="16" /></span>
        </div>
    </div>
</a>
