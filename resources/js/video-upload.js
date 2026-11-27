// Lesson video upload for the teacher and admin areas: straight from the browser to Bunny Stream with tus
// (resumable). Loaded only in /teach and /admin, never on the student site.
import * as tus from 'tus-js-client';

window.zirakVideoUpload = (config) => ({
    state: config.status, // none | ready | uploading | paused | processing | failed | error
    percent: 0,
    progress: config.progress ?? null,
    error: '',
    upload: null,
    file: null,
    timer: null,

    init() {
        if (this.state === 'processing') this.poll();
        if (this.state === 'uploading') this.state = 'paused'; // a page reload interrupted it: choose the file again to continue
    },

    pct(value) {
        const n = Number(value || 0).toLocaleString(config.numbers);
        return config.numbers === 'fa' ? n + '٪' : n + '%';
    },

    csrf() {
        return document.querySelector('meta[name=csrf-token]')?.content ?? '';
    },

    async post(url) {
        const res = await fetch(url, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.csrf() } });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || config.text.bunny_error);
        return data;
    },

    choose(event) {
        const file = event.target.files[0];
        event.target.value = '';
        if (!file) return;
        if (!file.type.startsWith('video/')) {
            this.error = config.text.not_video;
            return;
        }
        this.file = file;
        this.start();
    },

    async start() {
        this.error = '';
        this.state = 'uploading';
        let creds;
        try {
            creds = await this.post(config.startUrl);
        } catch (e) {
            this.state = 'error';
            this.error = e.message;
            return;
        }
        const videoId = creds.headers.VideoId;
        this.upload = new tus.Upload(this.file, {
            endpoint: creds.endpoint,
            headers: creds.headers,
            metadata: { filetype: this.file.type, title: creds.title },
            chunkSize: 5 * 1024 * 1024, // small pieces: a dropped connection only loses the last piece
            retryDelays: [0, 3000, 5000, 10000, 20000, 60000, 60000, 120000, 120000],
            removeFingerprintOnSuccess: true,
            // Resume only an earlier upload of this same file to this same Bunny video.
            fingerprint: async (file) => ['zirak', videoId, file.name, file.size, file.lastModified].join('-'),
            onProgress: (sent, total) => (this.percent = Math.floor((sent / total) * 100)),
            onError: () => {
                this.state = 'paused';
                this.error = config.text.failed_retry;
            },
            onSuccess: async () => {
                this.percent = 100;
                this.state = 'processing';
                try {
                    const status = await this.post(config.finishedUrl);
                    this.apply(status);
                } catch (e) {}
                this.poll();
            },
        });
        const previous = await this.upload.findPreviousUploads();
        if (previous.length) this.upload.resumeFromPreviousUpload(previous[0]);
        this.upload.start();
    },

    pause() {
        this.upload?.abort();
        this.state = 'paused';
    },

    resume() {
        this.error = '';
        if (this.upload && this.file) {
            this.state = 'uploading';
            this.upload.start();
        }
    },

    apply(data) {
        this.state = data.status === 'none' ? 'none' : data.status;
        this.progress = data.progress;
        if (this.state === 'ready' || this.state === 'failed') clearInterval(this.timer);
        if (this.state === 'ready') this.$dispatch('zirak-video-ready');
    },

    poll() {
        clearInterval(this.timer);
        this.timer = setInterval(async () => {
            try {
                const res = await fetch(config.statusUrl, { headers: { Accept: 'application/json' } });
                if (res.ok) this.apply(await res.json());
            } catch (e) {}
        }, 10000);
    },

    destroy() {
        clearInterval(this.timer);
    },
});

// Warn before leaving the page in the middle of an upload.
window.addEventListener('beforeunload', (event) => {
    if (document.querySelector('[data-video-uploading="1"]')) {
        event.preventDefault();
        event.returnValue = '';
    }
});
