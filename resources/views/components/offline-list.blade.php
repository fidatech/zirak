<section x-data="offlineList({ numbers: @js(app()->getLocale() === 'en' ? 'en' : 'fa') })" {{ $attributes->merge(['class' => 'flex flex-col gap-3 rounded-3xl border border-slate-200 bg-white p-6 text-start']) }}>
    <div class="flex items-center gap-2 font-extrabold"><x-svg-icon name="download" :size="20" class="text-brand-600" />{{ __('app.offline.saved_title') }}</div>
    <p x-show="! items.length" class="text-sm text-slate-600">{{ __('app.offline.saved_empty') }}</p>
    <template x-for="item in items" :key="item.key">
        <div class="flex items-center gap-3 border-t border-slate-100 pt-3">
            <a :href="item.url" class="min-w-0 flex-1">
                <div class="truncate font-bold text-brand-600" x-text="item.title"></div>
                <div class="truncate text-xs text-slate-600"><span x-text="item.course"></span> · <bdi x-text="size(item.bytes)"></bdi></div>
            </a>
            <button type="button" @click="remove(item.key)" class="text-sm text-slate-600 underline">{{ __('app.offline.remove') }}</button>
        </div>
    </template>
    <div x-show="items.length" class="text-xs text-slate-500" x-text="@js(__('app.offline.total', ['size' => '__S__'])).replace('__S__', size(total))"></div>
    <div x-show="pending" x-cloak class="rounded-xl bg-amber-50 p-3 text-sm text-amber-900" x-text="@js(__('app.offline.pending', ['n' => '__N__'])).replace('__N__', pending.toLocaleString(@js(app()->getLocale() === 'en' ? 'en' : 'fa')))"></div>
</section>
