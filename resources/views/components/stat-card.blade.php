@props(['emoji', 'label', 'value', 'tint' => 'bg-brand-50', 'trend' => null, 'trendText' => null])
{{-- clast-style stat: tinted icon tile, label, big value, trend chip --}}
<div {{ $attributes->merge(['class' => 'shadow-soft flex flex-col items-start gap-2 rounded-2xl bg-white p-4 ring-1 ring-slate-100 sm:flex-row sm:items-center sm:gap-3']) }}>
    <span @class(['flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl text-2xl sm:h-14 sm:w-14 sm:text-3xl', $tint]) aria-hidden="true">{{ $emoji }}</span>
    <div class="min-w-0 flex-1">
        <div class="text-sm leading-6 text-slate-500">{{ $label }}</div>
        <div class="text-2xl font-black leading-tight">{{ $value }}</div>
        @if ($trendText)
            <span @class(['mt-1 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold leading-5 sm:text-xs',
                'bg-emerald-50 text-emerald-700' => $trend === null || $trend >= 0,
                'bg-rose-50 text-rose-700' => $trend !== null && $trend < 0])>
                @if ($trend !== null){{ $trend >= 0 ? '↑' : '↓' }}@endif {{ $trendText }}
            </span>
        @endif
    </div>
</div>
