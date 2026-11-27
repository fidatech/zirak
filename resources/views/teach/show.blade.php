<x-public-page :title="$teacher->publicName()">
    @guest<div class="h-24"></div>@endguest
    <div class="mx-auto flex max-w-5xl flex-col gap-6 px-4 py-6 lg:px-8">
        <section class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 sm:flex-row sm:items-center">
            <span class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-700"><x-svg-icon name="user" :size="40" /></span>
            <div class="flex flex-1 flex-col gap-1">
                <div class="text-sm text-slate-500">{{ __('app.course.instructor') }}</div>
                <h1 class="text-2xl font-extrabold">{{ $teacher->publicName() }}</h1>
                @if ($teacher->teacher_headline)<div class="text-slate-700">{{ $teacher->teacher_headline }}</div>@endif
                <div class="text-sm text-slate-500">{{ __('app.teach.stats', ['courses' => n($courses->count()), 'students' => n($students)]) }}</div>
            </div>
        </section>
        @if ($teacher->teacher_bio)
            <section class="whitespace-pre-line rounded-3xl border border-slate-200 bg-white p-6 leading-loose text-slate-700">{{ $teacher->teacher_bio }}</section>
        @endif
        <section class="flex flex-col gap-3">
            <h2 class="text-xl font-extrabold">{{ __('app.teach.courses_by') }}</h2>
            @if ($courses->isEmpty())
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600">{{ __('app.teach.no_courses_yet') }}</div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($courses as $course)
                        <x-course-card :course="$course" />
                    @endforeach
                </div>
            @endif
        </section>
    </div>
</x-public-page>
