@props(['category', 'size' => 64])
@php([$soft, $strong] = $category->palette())
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 64 64" aria-hidden="true" {{ $attributes }}>
    <circle cx="32" cy="32" r="30" fill="#fff"/>
    @switch($category->icon)
        @case('film')<rect x="14" y="22" width="26" height="20" rx="4" fill="{{ $strong }}"/><path d="m40 28 10-5v18l-10-5z" fill="{{ $strong }}"/>@break
        @case('story')<path d="M16 18h32v22H26l-10 8z" fill="{{ $strong }}"/><path d="M23 26h18M23 32h11" stroke="#fff" stroke-width="3" stroke-linecap="round"/>@break
        @case('code')<rect x="12" y="16" width="40" height="30" rx="5" fill="{{ $strong }}"/><path d="m26 26-5 5 5 5M38 26l5 5-5 5" stroke="#fff" stroke-width="3" fill="none" stroke-linecap="round"/>@break
        @case('palette')<circle cx="26" cy="28" r="10" fill="{{ $strong }}"/><rect x="30" y="30" width="16" height="16" rx="3" fill="{{ $strong }}" opacity=".7"/><path d="m14 48 8-12 8 12z" fill="{{ $strong }}"/>@break
        @case('book')<path d="M14 18h16a4 4 0 0 1 4 4v24a4 4 0 0 0-4-4H14zM50 18H38a4 4 0 0 0-4 4v24a4 4 0 0 1 4-4h12z" fill="{{ $strong }}"/>@break
        @case('camera')<rect x="12" y="22" width="40" height="26" rx="5" fill="{{ $strong }}"/><path d="M24 22l3-5h10l3 5" fill="{{ $strong }}"/><circle cx="32" cy="35" r="7" fill="#fff"/>@break
        @case('music')<path d="M26 44V20l18-4v24" stroke="{{ $strong }}" stroke-width="4" fill="none"/><circle cx="22" cy="44" r="5" fill="{{ $strong }}"/><circle cx="40" cy="40" r="5" fill="{{ $strong }}"/>@break
        @case('chart')<path d="M16 46V30M26 46V20M36 46V34M46 46V24" stroke="{{ $strong }}" stroke-width="6" stroke-linecap="round"/>@break
        @case('language')<rect x="12" y="16" width="24" height="20" rx="4" fill="{{ $strong }}"/><rect x="28" y="28" width="24" height="20" rx="4" fill="{{ $strong }}" opacity=".6"/>@break
        @default<rect x="18" y="18" width="28" height="28" rx="6" fill="{{ $strong }}"/><circle cx="27" cy="30" r="3" fill="#fff"/><circle cx="37" cy="30" r="3" fill="#fff"/><path d="M26 38h12" stroke="#fff" stroke-width="3" stroke-linecap="round"/>
    @endswitch
</svg>
