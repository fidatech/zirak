@php($max = max(1, collect($report['days'])->max('minutes')))
<x-layouts.app :title="__('app.report.title')">
    <div class="mx-auto flex max-w-5xl flex-col gap-5 px-4 py-6 lg:px-8">
        <div class="flex flex-wrap items-center gap-3">
            <div class="flex-1"><h1 class="text-2xl font-extrabold">{{ __('app.report.title') }}</h1><p class="text-sm text-slate-600">{{ auth()->user()->displayName() }} · {{ $report['days'][0]['date']->translatedFormat('j F') }} – {{ $report['days'][6]['date']->translatedFormat('j F') }}</p></div>
            <button onclick="window.print()" class="no-print rounded-xl border border-slate-300 bg-white px-4 py-2 font-bold">{{ __('app.report.print') }}</button>
        </div>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            @foreach ([['time', n($report['minutes']).' '.__('app.minutes')], ['lessons', n($report['lessons'])], ['avg', $report['average_score'] !== null ? pct($report['average_score']) : '—'], ['streak', n($report['streak'])]] as [$key, $value])
                <div class="rounded-2xl border border-slate-200 bg-white p-4"><div class="text-sm text-slate-600">{{ __('app.report.'.$key) }}</div><div class="mt-1 text-2xl font-extrabold">{{ $value }}</div></div>
            @endforeach
        </div>
        <div class="flex flex-col gap-4 md:flex-row">
            <section class="flex-1 rounded-2xl border border-slate-200 bg-white p-5">
                <h2 class="mb-4 font-extrabold">{{ __('app.report.per_day') }}</h2>
                <div class="flex h-44 items-end gap-2 border-b border-slate-200">
                    @foreach ($report['days'] as $day)
                        <div class="flex flex-1 flex-col items-center gap-1">
                            <span class="text-xs text-slate-600">{{ n($day['minutes']) }}</span>
                            <span class="w-full rounded-t-lg {{ $day['minutes'] === $max && $max > 1 ? 'bg-brand-600' : 'bg-brand-300' }}" style="height: {{ max(2, round($day['minutes'] / $max * 130)) }}px"></span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-1 flex gap-2 text-center text-xs text-slate-600">
                    @foreach ($report['days'] as $day)<span class="flex-1">{{ $day['date']->translatedFormat('D') }}</span>@endforeach
                </div>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 md:w-80">
                <h2 class="mb-3 font-extrabold">{{ __('app.report.weak') }}</h2>
                @forelse ($weak as $topic)
                    <a href="{{ route('chapters.show', [$topic['chapter']->course, $topic['chapter'], 'practice']) }}" class="flex justify-between py-1.5 text-sm"><span>{{ $topic['chapter']->title }}</span><strong @class(['text-amber-700' => $topic['percent'] < 70, 'text-green-700' => $topic['percent'] >= 70])>{{ pct($topic['percent']) }}</strong></a>
                @empty
                    <p class="text-sm text-slate-500">—</p>
                @endforelse
            </section>
        </div>
        <section class="rounded-2xl border border-slate-200 bg-white p-5">
            <h2 class="mb-3 font-extrabold">{{ __('app.report.my_courses') }}</h2>
            @forelse ($courses as $row)
                <a href="{{ route('courses.show', $row['course']) }}" class="flex items-center gap-3 border-t border-slate-100 py-3 first:border-0">
                    <span class="flex-1 font-bold">{{ $row['course']->title }}</span>
                    <x-progress :value="$row['state']['percent']" class="max-w-32" />
                    <span class="w-12 text-sm">{{ pct($row['state']['percent']) }}</span>
                </a>
            @empty
                <p class="text-sm text-slate-500">{{ __('app.home.no_courses') }}</p>
            @endforelse
        </section>
    </div>
</x-layouts.app>
