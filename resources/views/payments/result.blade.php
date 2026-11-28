<x-layouts.app>
    <div class="mx-auto flex max-w-md flex-col items-center gap-4 px-4 py-12 text-center">
        @if ($status === 'success')
            <x-svg-icon name="check" :size="56" class="text-green-600" />
            <h1 class="text-xl font-extrabold">{{ __('app.pay.success') }}</h1>
            <a href="{{ $order->itemUrl() }}" class="rounded-xl bg-brand-600 px-6 py-3 font-bold text-white">{{ __('app.course.continue') }}</a>
        @elseif ($status === 'pending')
            <h1 class="text-xl font-extrabold">{{ __('app.pay.pending') }}</h1>
            <a href="{{ request()->fullUrl() }}" class="rounded-xl border border-slate-300 bg-white px-6 py-3 font-bold">↻</a>
        @else
            <h1 class="text-xl font-extrabold text-red-800">{{ __('app.pay.failed') }}</h1>
            <a href="{{ $order->itemUrl() }}" class="rounded-xl bg-brand-600 px-6 py-3 font-bold text-white">{{ __('app.back') }}</a>
        @endif
    </div>
</x-layouts.app>
