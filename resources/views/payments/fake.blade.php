<x-layouts.app :title="__('app.pay.fake_title')">
    <div class="mx-auto flex max-w-md flex-col gap-4 px-4 py-10">
        <div class="rounded-3xl border-2 border-dashed border-amber-400 bg-white p-6">
            <h1 class="text-xl font-extrabold">{{ __('app.pay.fake_title') }}</h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('app.pay.fake_text') }}</p>
            <div class="mt-4 rounded-xl bg-slate-50 p-4"><div class="font-bold">{{ $order->course->title }}</div><div class="text-2xl font-extrabold">{{ afn($order->total) }}</div><div class="text-xs text-slate-500" dir="ltr">{{ $order->number }}</div></div>
            <form method="POST" action="{{ route('payments.fake.pay', $order->number) }}" class="mt-4 flex gap-2">@csrf
                <button class="flex-1 rounded-xl bg-brand-600 py-3 font-bold text-white">{{ __('app.pay.pay', ['amount' => afn($order->total)]) }}</button>
                <button name="cancel" value="1" class="rounded-xl border border-slate-300 px-4">{{ __('app.pay.cancel') }}</button>
            </form>
        </div>
    </div>
</x-layouts.app>
