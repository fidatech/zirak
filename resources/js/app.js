import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Practice: one question at a time with instant feedback from the server.
Alpine.data('practice', (config) => ({
    questions: config.questions,
    index: config.start,
    url: config.url,
    chosen: null,
    result: null,
    busy: false,
    correctCount: config.correct ?? 0,
    get question() { return this.questions[this.index] ?? null; },
    get finished() { return this.index >= this.questions.length; },
    async choose(i) {
        if (this.result || this.busy) return;
        this.busy = true;
        this.chosen = i;
        try {
            const res = await fetch(this.url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ question_id: this.question.id, choice: i }),
            });
            this.result = await res.json();
            if (this.result.correct) this.correctCount++;
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
