@props(['level', 'highlight' => false])
{{-- One step of the AI path. Colours deepen from Level 1 to Level 5. --}}
@php
    $tints = [['#E7E0FA', '#2E1A63'], ['#D7CBF6', '#2E1A63'], ['#B9A6EE', '#2E1A63'], ['#7A5BD6', '#FFFFFF'], ['#4B2A99', '#FFFFFF']];
    [$bg, $fg] = $tints[min($level->number, 5) - 1];
    $price = $level->priceLabel();
@endphp
<article id="level-{{ $level->number }}" @class(['flex min-w-0 scroll-mt-28 flex-col overflow-hidden rounded-2xl border bg-white', 'border-accent-400 ring-4 ring-accent-400/40' => $highlight, 'border-line' => ! $highlight])>
    <div class="flex flex-col gap-1.5 px-5 pb-4 pt-5" style="background: {{ $bg }}; color: {{ $fg }}">
        <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-extrabold uppercase tracking-wider">{{ __('app.levels.level_n', ['n' => n($level->number)]) }}</span>
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white text-base font-black text-ink">{{ n($level->number) }}</span>
        </div>
        @if ($highlight)
            <span class="self-start rounded-md bg-accent-400 px-2 py-0.5 text-xs font-extrabold text-accent-900">{{ __('app.levels.recommended') }}</span>
        @endif
        <h3 class="text-xl font-extrabold leading-snug">{{ $level->text('title') }}</h3>
        @if ($level->text('tagline'))<div class="text-sm font-semibold opacity-90">{{ $level->text('tagline') }}</div>@endif
    </div>
    <div class="flex flex-1 flex-col gap-3 px-5 pb-5 pt-4 text-sm leading-relaxed text-muted">
        @if ($level->text('who'))<p><strong class="text-ink">{{ __('app.levels.who') }}</strong> {{ $level->text('who') }}</p>@endif
        @if ($learn = $level->learnList())
            <ul class="flex flex-col gap-1.5 text-ink">
                @foreach ($learn as $item)
                    <li class="flex items-start gap-2"><x-svg-icon name="check" :size="16" class="mt-1 shrink-0 text-brand-600" /><span>{{ $item }}</span></li>
                @endforeach
            </ul>
        @endif
        @if ($level->text('after'))<p><strong class="text-ink">{{ __('app.levels.after') }}</strong> {{ $level->text('after') }}</p>@endif
        <div class="mt-auto flex flex-wrap gap-x-4 gap-y-1 border-t border-line pt-3 text-xs">
            @if ($level->text('duration'))<span class="flex items-center gap-1.5"><x-svg-icon name="calendar" :size="14" />{{ $level->text('duration') }}</span>@endif
            <span class="flex items-center gap-1.5"><x-svg-icon name="award" :size="14" />{{ __('app.levels.certificate') }}</span>
        </div>
        <div class="flex items-center justify-between gap-2">
            <span class="text-base font-extrabold text-ink">{{ $price }}</span>
            <a href="{{ $level->url() }}" class="flex items-center gap-1 font-bold text-brand-600 hover:text-brand-800">{{ __('app.levels.open', ['n' => n($level->number)]) }}<x-svg-icon name="forward" :size="15" class="rtl:rotate-180" /></a>
        </div>
    </div>
</article>
