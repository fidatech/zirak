@props(['category'])
@php([$soft, $strong, $dark] = $category->palette())
<a href="{{ route('catalog.category', $category) }}" class="flex items-center gap-3 rounded-3xl p-4 transition hover:-translate-y-0.5" style="background: {{ $soft }}">
    <x-subject-art :category="$category" :size="56" class="shrink-0" />
    <span class="flex min-w-0 flex-col">
        <strong class="text-base">{{ $category->name() }}</strong>
        <span class="text-sm" style="color: {{ $dark }}">{{ n($category->courses_count ?? 0) }} {{ __('app.courses') }}</span>
    </span>
</a>
