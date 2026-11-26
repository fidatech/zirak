@props(['name', 'size' => 22])
<svg {{ $attributes->merge(['width' => $size, 'height' => $size]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
    @case('home')<path d="M3 10.5 12 3l9 7.5V21h-6v-6H9v6H3z"/>@break
    @case('book')<path d="M4 5a2 2 0 0 1 2-2h14v16H6a2 2 0 0 0-2 2zM4 21V5"/>@break
    @case('grid')<path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/>@break
    @case('chart')<path d="M4 20V10M10 20V4M16 20v-7M2 20h20"/>@break
    @case('award')<circle cx="12" cy="9" r="6"/><path d="m8.5 14-1.5 7 5-3 5 3-1.5-7"/>@break
    @case('user')<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>@break
    @case('search')<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>@break
    @case('play')<path d="M8 5v14l11-7z" fill="currentColor" stroke="none"/>@break
    @case('check')<path d="m5 12 5 5L19 7"/>@break
    @case('lock')<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/>@break
    @case('chat')<path d="M4 5h16v11H9l-5 4z"/>@break
    @case('flame')<path d="M12 2c1 4 6 5.5 6 11a6 6 0 0 1-12 0c0-3.5 2.5-5 2.5-8 2 1.3 3.5 1 3.5-3z" fill="currentColor" stroke="none"/>@break
    @case('globe')<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>@break
    @case('back')<path d="{{ text_dir() === 'rtl' ? 'm9 6 6 6-6 6' : 'm15 6-6 6 6 6' }}"/>@break
    @case('forward')<path d="{{ text_dir() === 'rtl' ? 'm15 6-6 6 6 6' : 'm9 6 6 6-6 6' }}"/>@break
    @case('pencil')<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>@break
    @case('logout')<path d="M15 4h4v16h-4M10 8l-4 4 4 4M6 12h10"/>@break
    @case('shield')<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>@break
    @default<circle cx="12" cy="12" r="9"/>
@endswitch
</svg>
