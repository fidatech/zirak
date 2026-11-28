@props(['value' => 0, 'color' => '#4B2A99', 'track' => '#EEF0F6'])
<div {{ $attributes->merge(['class' => 'h-2 w-full overflow-hidden rounded-full']) }} style="background: {{ $track }}" role="progressbar" aria-valuenow="{{ $value }}" aria-valuemin="0" aria-valuemax="100">
    <div class="h-full rounded-full" style="width: {{ max(0, min(100, $value)) }}%; background: {{ $color }}"></div>
</div>
