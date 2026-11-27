@php
    $config = [
        'status' => $lesson->video_status ?? ($lesson->video_id ? 'ready' : 'none'),
        'progress' => $lesson->video_progress,
        'startUrl' => route('video.start', $lesson),
        'finishedUrl' => route('video.finished', $lesson),
        'statusUrl' => route('video.status', $lesson),
        'numbers' => in_array(app()->getLocale(), ['prs', 'ps'], true) ? 'fa' : 'en',
        'text' => [
            'not_video' => __('teach.video.not_video'),
            'failed_retry' => __('teach.video.failed_retry'),
            'bunny_error' => __('teach.video.bunny_error'),
        ],
    ];
@endphp
<style>
    .zv { display: flex; flex-direction: column; gap: 1rem; font-size: .875rem; line-height: 1.6; }
    .zv-status { font-weight: 600; }
    .zv-ok { color: var(--success-600); } .zv-muted { color: var(--gray-500); } .zv-info { color: var(--primary-600); } .zv-bad { color: var(--danger-600); }
    .zv-bar { height: .75rem; border-radius: 999px; background: var(--gray-200); overflow: hidden; }
    .zv-bar > div { height: 100%; border-radius: 999px; background: var(--primary-600); transition: width .3s; }
    .zv-row { display: flex; align-items: center; justify-content: space-between; gap: .75rem; }
    .zv-msg { border-radius: .5rem; padding: .75rem; }
    .zv-warn { background: var(--warning-50); color: var(--warning-800); }
    .zv-err { background: var(--danger-50); color: var(--danger-700); }
    .zv-drop { display: flex; flex-direction: column; align-items: center; gap: .5rem; padding: 1.5rem; text-align: center; cursor: pointer;
        border: 2px dashed var(--gray-300); border-radius: .75rem; }
    .zv-drop:hover { border-color: var(--primary-600); }
    .zv-drop strong { color: var(--primary-600); }
    .zv-file { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
</style>
<div x-data="zirakVideoUpload(@js($config))" class="zv" :data-video-uploading="state === 'uploading' ? 1 : 0">
    @unless ($configured)
        <div class="zv-msg zv-warn">{{ __('teach.video.not_configured') }}</div>
    @else
        <div class="zv-status">
            <span x-show="state === 'ready'" class="zv-ok">✓ {{ __('teach.video.ready') }}</span>
            <span x-show="state === 'none'" class="zv-muted">{{ __('teach.video.none') }}</span>
            <span x-show="state === 'processing'" class="zv-info" x-text="@js(__('teach.video.processing', ['percent' => '__P__'])).replace('__P__', pct(progress ?? 0))"></span>
            <span x-show="state === 'failed'" class="zv-bad">{{ __('teach.video.failed') }}</span>
        </div>

        <template x-if="state === 'uploading' || state === 'paused'">
            <div class="zv">
                <div class="zv-bar"><div :style="`width: ${percent}%`"></div></div>
                <div class="zv-row">
                    <span x-text="(state === 'paused' ? @js(__('teach.video.paused')) : @js(__('teach.video.uploading'))) + ' ' + pct(percent)"></span>
                    <x-filament::button size="sm" color="gray" x-show="state === 'uploading'" x-on:click="pause()">{{ __('teach.video.pause') }}</x-filament::button>
                    <x-filament::button size="sm" x-show="state === 'paused' && upload" x-on:click="resume()">{{ __('teach.video.resume') }}</x-filament::button>
                </div>
            </div>
        </template>

        <p x-show="error" x-text="error" class="zv-msg zv-err"></p>

        <label x-show="state !== 'uploading'" class="zv-drop">
            <strong x-text="state === 'ready' || state === 'processing' ? @js(__('teach.video.replace')) : @js(__('teach.video.choose'))"></strong>
            <span class="zv-muted">{{ __('teach.video.hint') }}</span>
            <input type="file" accept="video/*" class="zv-file" x-on:change="choose($event)">
        </label>
        <p class="zv-muted" x-show="state === 'ready' || state === 'processing'">{{ __('teach.video.students_note') }}</p>
    @endunless
</div>
