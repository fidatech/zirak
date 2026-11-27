@props(['title', 'icon' => null, 'link' => null, 'linkText' => null])
<section {{ $attributes->merge(['class' => 'shadow-soft rounded-2xl bg-white p-5 ring-1 ring-slate-100']) }}>
    <header class="mb-4 flex items-center gap-2">
        @if ($icon)<span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-600"><x-svg-icon :name="$icon" :size="16" /></span>@endif
        <h2 class="flex-1 font-bold">{{ $title }}</h2>
        @if ($link)<a href="{{ $link }}" class="text-sm font-semibold text-brand-600 hover:text-brand-700">{{ $linkText }}</a>@endif
    </header>
    {{ $slot }}
</section>
