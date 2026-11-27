@props(['title' => null])
{{-- Public pages (courses, teachers): the student app frame when logged in, the landing top bar + footer for visitors. --}}
@auth
    <x-layouts.app :title="$title">{{ $slot }}</x-layouts.app>
@else
    <x-layouts.app :title="$title" landing>
        <x-site-nav />
        {{ $slot }}
        <x-site-footer />
    </x-layouts.app>
@endauth
