<x-layouts.app :title="__('app.offline.title')">
    <div class="mx-auto flex max-w-md flex-col items-center gap-3 px-4 py-16 text-center">
        <x-svg-icon name="cloud-off" :size="48" class="text-slate-400" />
        <h1 class="text-xl font-extrabold">{{ __('app.offline.title') }}</h1>
        <p class="text-slate-600">{{ __('app.offline.text') }}</p>
        <x-offline-list class="mt-4 w-full" />
    </div>
</x-layouts.app>
