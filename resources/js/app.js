import Alpine from 'alpinejs';

window.Alpine = Alpine;

// ---------- Offline: saved chapters, and answers waiting for the internet ----------
const OFFLINE_CACHE = 'zirak-offline';
const SAVED = 'zirak.saved';
const QUEUE = 'zirak.queue';
const store = {
    get(key, fallback) { try { return JSON.parse(localStorage.getItem(key)) ?? fallback; } catch (e) { return fallback; } },
    set(key, value) { try { localStorage.setItem(key, JSON.stringify(value)); return true; } catch (e) { return false; } },
};
const csrf = () => document.querySelector('meta[name=csrf-token]')?.content ?? '';
const changed = () => window.dispatchEvent(new CustomEvent('zirak-offline-changed'));

function queueRequest(url, body) {
    const q = store.get(QUEUE, []);
    q.push({ url, body, at: Date.now() });
    store.set(QUEUE, q);
    changed();
}

let syncing = false;
async function syncQueue() {
    if (syncing || !navigator.onLine) return;
    const q = store.get(QUEUE, []);
    if (!q.length) return;
    syncing = true;
    const left = [];
    const maxAge = 30 * 24 * 3600 * 1000;
    for (const item of q) {
        if (Date.now() - item.at > maxAge) continue;
        try {
            const res = await fetch(item.url, {
                method: 'POST',
                redirect: 'manual',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: JSON.stringify(item.body),
            });
            // Keep for later only when trying again can help (expired page token, busy server).
            if (res.status === 419 || res.status === 429 || res.status >= 500) left.push(item);
        } catch (e) {
            left.push(item);
        }
    }
    // Answers given while syncing stay in the queue.
    const added = store.get(QUEUE, []).slice(q.length);
    store.set(QUEUE, left.concat(added));
    syncing = false;
    changed();
}
window.addEventListener('online', syncQueue);
window.addEventListener('load', syncQueue);

async function clearOffline() {
    try { if ('caches' in window) await Promise.all((await caches.keys()).map((k) => caches.delete(k))); } catch (e) {}
    try { localStorage.removeItem(SAVED); localStorage.removeItem(QUEUE); } catch (e) {}
}

// Shared phones: logging out removes everything saved on this phone.
document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!form.matches('[data-logout]') || form.dataset.cleared) return;
    event.preventDefault();
    await clearOffline();
    form.dataset.cleared = '1';
    form.submit();
});

// "Save for offline" button on a chapter.
Alpine.data('saveOffline', (pack) => ({
    supported: 'caches' in window,
    saved: false,
    busy: false,
    failed: false,
    init() {
        this.saved = Boolean(store.get(SAVED, {})[pack.key]);
    },
    async save() {
        if (this.busy) return;
        this.busy = true;
        this.failed = false;
        try {
            const cache = await caches.open(OFFLINE_CACHE);
            const assets = new Set();
            let bytes = 0;
            for (const url of [...pack.urls, '/offline']) {
                const res = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'text/html' } });
                if (!res.ok || res.redirected) throw new Error('page');
                const html = await res.text();
                if (url !== '/offline') bytes += new Blob([html]).size;
                for (const m of html.matchAll(/(?:href|src)="([^"]*\/build\/[^"]+)"/g)) assets.add(new URL(m[1], location.href).pathname);
                await cache.put(url, new Response(html, { headers: { 'Content-Type': 'text/html; charset=UTF-8' } }));
            }
            // Keep this version's CSS and JS too, so saved pages still look right after a site update.
            for (const asset of assets) {
                const res = await fetch(asset);
                if (res.ok) {
                    const blob = await res.clone().blob();
                    bytes += blob.size;
                    await cache.put(asset, res);
                }
            }
            const saved = store.get(SAVED, {});
            saved[pack.key] = { title: pack.title, course: pack.course, url: pack.url, urls: pack.urls, bytes, at: Date.now() };
            if (!store.set(SAVED, saved)) throw new Error('storage');
            this.saved = true;
            changed();
        } catch (e) {
            this.failed = true;
        } finally {
            this.busy = false;
        }
    },
    async remove() {
        await removeSaved(pack.key);
        this.saved = false;
    },
}));

async function removeSaved(key) {
    const saved = store.get(SAVED, {});
    const item = saved[key];
    if (item && 'caches' in window) {
        const cache = await caches.open(OFFLINE_CACHE);
        await Promise.all(item.urls.map((u) => cache.delete(u)));
    }
    delete saved[key];
    store.set(SAVED, saved);
    if (!Object.keys(saved).length && 'caches' in window) await caches.delete(OFFLINE_CACHE);
    changed();
}

// List of saved chapters (profile page and the offline page).
Alpine.data('offlineList', (config) => ({
    items: [],
    pending: 0,
    init() {
        this.load();
        window.addEventListener('zirak-offline-changed', () => this.load());
    },
    load() {
        this.items = Object.entries(store.get(SAVED, {})).map(([key, v]) => ({ key, ...v })).sort((a, b) => b.at - a.at);
        this.pending = store.get(QUEUE, []).length;
    },
    size(bytes) {
        const n = (v) => v.toLocaleString(config.numbers, { maximumFractionDigits: 1 });
        return bytes >= 1024 * 1024 ? n(bytes / 1024 / 1024) + ' MB' : n(Math.max(1, Math.round(bytes / 1024))) + ' KB';
    },
    get total() { return this.items.reduce((sum, i) => sum + (i.bytes || 0), 0); },
    async remove(key) { await removeSaved(key); },
}));

// Online / offline bar at the top of every page.
Alpine.data('connection', () => ({
    online: navigator.onLine,
    pending: store.get(QUEUE, []).length,
    init() {
        window.addEventListener('online', () => (this.online = true));
        window.addEventListener('offline', () => (this.online = false));
        window.addEventListener('zirak-offline-changed', () => (this.pending = store.get(QUEUE, []).length));
    },
}));

// "Mark as complete" works offline too: it is sent when the internet is back.
Alpine.data('completeLesson', () => ({
    queued: false,
    submit(event) {
        if (navigator.onLine) return;
        event.preventDefault();
        queueRequest(event.target.action, {});
        this.queued = true;
    },
}));

// Practice: one question at a time with instant feedback from the server.
Alpine.data('practice', (config) => ({
    questions: config.questions,
    index: config.start,
    url: config.url,
    chosen: null,
    result: null,
    busy: false,
    correctCount: config.correct ?? 0,
    pendingCount: 0,
    get question() { return this.questions[this.index] ?? null; },
    get finished() { return this.index >= this.questions.length; },
    async choose(i) {
        if (this.result || this.busy) return;
        this.busy = true;
        this.chosen = i;
        const body = { question_id: this.question.id, choice: i };
        try {
            const res = await fetch(this.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf(),
                },
                body: JSON.stringify(body),
            });
            if (!res.ok) throw new Error('server');
            this.result = await res.json();
            if (this.result.correct) this.correctCount++;
        } catch (e) {
            // No internet: keep the answer on the phone. The right answer is only shown by the server, later.
            if (navigator.onLine && !(e instanceof TypeError)) { this.chosen = null; return; }
            queueRequest(this.url, body);
            this.result = { pending: true };
            this.pendingCount++;
        } finally {
            this.busy = false;
        }
    },
    next() {
        this.index++;
        this.chosen = null;
        this.result = null;
    },
    optionClass(i) {
        if (!this.result) return 'border-slate-200 bg-white hover:border-brand-600';
        if (this.result.pending) return i === this.chosen ? 'border-brand-600 bg-brand-50' : 'border-slate-200 bg-white opacity-70';
        if (i === this.result.correct_index) return 'border-green-600 bg-green-50';
        if (i === this.chosen) return 'border-red-600 bg-red-50';
        return 'border-slate-200 bg-white opacity-70';
    },
}));

Alpine.data('copy', () => ({
    copied: false,
    async copy(text) {
        try { await navigator.clipboard.writeText(text); this.copied = true; setTimeout(() => (this.copied = false), 1500); } catch (e) {}
    },
}));

// AI tutor chat for one course.
Alpine.data('tutor', (config) => ({
    messages: [],
    question: '',
    remaining: null,
    available: true,
    busy: false,
    error: null,
    loaded: false,
    rated: {},
    async request(url, body = null) {
        const res = await fetch(url, {
            method: body ? 'POST' : 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            },
            body: body ? JSON.stringify(body) : null,
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const first = data.errors ? Object.values(data.errors)[0][0] : null;
            throw new Error(first || config.errorText);
        }
        return data;
    },
    async init() {
        try {
            const data = await this.request(config.historyUrl);
            this.messages = data.messages;
            this.remaining = data.remaining;
            this.available = data.available;
        } catch (e) {
            this.available = false;
        }
        this.loaded = true;
        this.$nextTick(() => this.scroll());
    },
    scroll() {
        if (this.$refs.list) this.$refs.list.scrollTop = this.$refs.list.scrollHeight;
    },
    async ask() {
        const q = this.question.trim();
        if (q.length < 2 || this.busy) return;
        this.busy = true;
        this.error = null;
        this.messages.push({ id: 'pending', role: 'student', content: q });
        this.question = '';
        this.$nextTick(() => this.scroll());
        try {
            const data = await this.request(config.askUrl, { question: q, lesson_id: config.lessonId });
            this.messages = this.messages.filter((m) => m.id !== 'pending').concat(data.messages);
            this.remaining = data.remaining;
        } catch (e) {
            this.messages = this.messages.filter((m) => m.id !== 'pending');
            this.question = q;
            this.error = e.message;
        } finally {
            this.busy = false;
            this.$nextTick(() => this.scroll());
        }
    },
    async escalate(message) {
        try {
            const data = await this.request(config.escalateUrl.replace('__ID__', message.id), {});
            this.messages.forEach((m) => { if (m.id === data.question_id) m.escalated = true; });
            message.escalated = true;
        } catch (e) {
            this.error = e.message;
        }
    },
    async rate(message, helpful) {
        this.rated[message.id] = true;
        message.helpful = helpful;
        try { await this.request(config.rateUrl.replace('__ID__', message.id), { helpful }); } catch (e) {}
    },
    questionOf(answer) {
        const i = this.messages.indexOf(answer);
        for (let j = i - 1; j >= 0; j--) if (this.messages[j].role === 'student') return this.messages[j];
        return null;
    },
}));

Alpine.start();

if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost')) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}
