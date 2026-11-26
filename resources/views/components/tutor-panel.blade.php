@props(['course', 'lesson' => null])
<div x-data="tutor({
        historyUrl: @js(route('tutor.history', $course)),
        askUrl: @js(route('tutor.ask', $course)),
        escalateUrl: @js(route('tutor.escalate', '__ID__')),
        rateUrl: @js(route('tutor.rate', '__ID__')),
        lessonId: @js($lesson?->id),
        errorText: @js(__('app.tutor.error')),
    })" {{ $attributes->merge(['class' => 'flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4']) }}>
    <div class="flex items-center gap-2">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-900 text-white"><x-svg-icon name="chat" :size="18" /></span>
        <div class="flex-1">
            <div class="font-extrabold">{{ __('app.tutor.title') }}</div>
            <div class="text-xs text-slate-500" x-show="remaining !== null && available" x-text="@js(__('app.tutor.remaining', ['count' => '__N__'])).replace('__N__', (remaining ?? 0).toLocaleString(@js(app()->getLocale() === 'en' ? 'en' : 'fa')))"></div>
        </div>
    </div>

    <template x-if="loaded && ! available">
        <p class="text-sm text-slate-600">{{ __('app.tutor.unavailable') }}</p>
    </template>

    <template x-if="available">
        <div class="flex flex-col gap-3">
            <p class="text-sm text-slate-600" x-show="messages.length === 0">{{ __('app.tutor.intro') }}</p>
            <div x-ref="list" class="flex max-h-96 flex-col gap-2 overflow-y-auto" aria-live="polite">
                <template x-for="m in messages" :key="m.id">
                    <div class="flex flex-col gap-1" :class="m.role === 'student' ? 'items-start' : 'items-end'">
                        <div class="max-w-[92%] rounded-2xl px-3 py-2 text-[14.5px] leading-relaxed"
                             :class="{
                                'bg-slate-100 rounded-ss-sm': m.role === 'student',
                                'border border-brand-300 bg-brand-50 rounded-se-sm': m.role === 'ai',
                                'border border-amber-300 bg-amber-50 rounded-se-sm': m.role === 'teacher',
                             }">
                            <span class="mb-0.5 block text-[11.5px] font-bold"
                                  :class="m.role === 'teacher' ? 'text-amber-800' : (m.role === 'ai' ? 'text-brand-600' : 'text-slate-500')"
                                  x-text="m.role === 'student' ? @js(__('app.tutor.you')) : (m.role === 'ai' ? @js(__('app.tutor.ai')) : @js(__('app.tutor.teacher')))"></span>
                            <span class="block whitespace-pre-line" x-text="m.content"></span>
                        </div>
                        <template x-if="m.role === 'ai'">
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <template x-if="m.helpful === null && ! rated[m.id]">
                                    <span class="flex gap-1">
                                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-slate-700" @click="rate(m, true)">{{ __('app.tutor.helpful') }}</button>
                                        <button type="button" class="rounded-lg border border-slate-200 px-2 py-1 text-slate-700" @click="rate(m, false)">{{ __('app.tutor.not_helpful') }}</button>
                                    </span>
                                </template>
                                <template x-if="m.helpful !== null || rated[m.id]"><span class="text-slate-500">{{ __('app.tutor.thanks') }}</span></template>
                                <template x-if="! (questionOf(m)?.escalated)">
                                    <button type="button" class="font-bold text-brand-600" @click="escalate(m)">{{ __('app.tutor.to_teacher') }}</button>
                                </template>
                                <template x-if="questionOf(m)?.escalated"><span class="text-amber-800">{{ __('app.tutor.sent_to_teacher') }}</span></template>
                            </div>
                        </template>
                    </div>
                </template>
                <template x-if="busy"><div class="self-end rounded-2xl bg-brand-50 px-3 py-2 text-sm text-brand-600">{{ __('app.tutor.thinking') }}</div></template>
            </div>
            <div class="rounded-xl bg-red-50 px-3 py-2 text-sm text-red-800" x-show="error" x-text="error" x-cloak></div>
            <form class="flex items-end gap-2" @submit.prevent="ask()">
                <label class="flex-1">
                    <span class="sr-only">{{ __('app.tutor.placeholder') }}</span>
                    <textarea x-model="question" rows="2" maxlength="1000" placeholder="{{ __('app.tutor.placeholder') }}"
                              @keydown.enter.prevent="if (! $event.shiftKey) ask()"
                              class="w-full resize-none rounded-xl border border-slate-300 px-3 py-2 text-[15px] outline-none focus:border-brand-600"></textarea>
                </label>
                <button class="h-11 rounded-xl bg-brand-600 px-4 font-bold text-white disabled:opacity-50" :disabled="busy || question.trim().length < 2 || remaining === 0">{{ __('app.tutor.send') }}</button>
            </form>
            <p class="text-[11.5px] text-slate-400">{{ __('app.tutor.disclaimer') }}</p>
        </div>
    </template>
</div>
