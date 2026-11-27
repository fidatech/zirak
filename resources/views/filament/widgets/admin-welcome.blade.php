<x-filament-widgets::widget>
    <div class="zk-welcome">
        <div class="relative z-10 flex flex-col gap-4 pe-24 sm:pe-36">
            <div>
                <h2 class="text-xl font-black tracking-tight text-gray-950 sm:text-2xl">Welcome back, {{ $name }}!</h2>
                <p class="mt-1 text-sm text-gray-600">{{ now()->format('l, j F Y') }} — here is what needs your attention at Zirak today.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach ($items as $item)
                    <a href="{{ $item['url'] }}" class="zk-pill text-gray-800 hover:text-primary-600">
                        <span aria-hidden="true">{{ $item['icon'] }}</span>{{ $item['label'] }}
                        <span class="zk-pill-count" @style(['background: #e2e8f0; color: #64748b' => ! $item['count']])>{{ $item['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        <div class="zk-welcome-emoji" aria-hidden="true">🧑‍💼</div>
    </div>
</x-filament-widgets::widget>
