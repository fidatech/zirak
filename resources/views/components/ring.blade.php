@props(['value' => 0, 'size' => 88, 'stroke' => 10, 'color' => 'url(#zirak-ring)'])
@php($r = ($size - $stroke) / 2)
@php($c = 2 * M_PI * $r)
@php($v = max(0, min(100, (int) $value)))
<div {{ $attributes->merge(['class' => 'relative shrink-0']) }} style="width: {{ $size }}px; height: {{ $size }}px" role="img" aria-label="{{ pct($v) }}">
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" class="-rotate-90">
        <defs><linearGradient id="zirak-ring" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#4B2A99"/><stop offset="1" stop-color="#4B2A99"/></linearGradient></defs>
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" fill="none" stroke="#EEF0F6" stroke-width="{{ $stroke }}"/>
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" fill="none" stroke="{{ $color }}" stroke-width="{{ $stroke }}" stroke-linecap="round"
                stroke-dasharray="{{ $c }}" stroke-dashoffset="{{ $c * (1 - $v / 100) }}"/>
    </svg>
    <span class="absolute inset-0 flex items-center justify-center text-lg font-black">{{ pct($v) }}</span>
</div>
