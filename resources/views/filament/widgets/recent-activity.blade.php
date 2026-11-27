<x-filament-widgets::widget>
    <x-filament::section heading="Recent activity" icon="heroicon-o-bolt">
        @if ($items->isEmpty())
            <p class="text-sm text-gray-500">Nothing yet. Enrollments, payments, certificates and teacher applications will appear here.</p>
        @else
            <div class="flex flex-col">
                @foreach ($items as $item)
                    <div class="zk-activity-item flex items-center gap-3 py-3 text-sm">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gray-50 text-lg" aria-hidden="true">{{ $item['icon'] }}</span>
                        <span class="min-w-0 flex-1 text-gray-700"><strong class="text-gray-950">{{ $item['who'] ?? 'Someone' }}</strong> {{ $item['text'] }} @if($item['what'])<strong class="text-gray-950">{{ $item['what'] }}</strong>@endif</span>
                        <span class="shrink-0 text-xs text-gray-500">{{ $item['at']->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
