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

Alpine.start();

if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost')) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}
