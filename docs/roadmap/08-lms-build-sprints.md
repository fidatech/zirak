# 08 — LMS build plan: 8 sprints × 2 weeks

Each sprint has tasks, a "done when" test, and a **build prompt** you can paste into Claude Code from the repo root (with `CLAUDE.md` from this pack in place). Work one prompt at a time, review the diff, run the tests, commit.

---

## Sprint 1 (weeks 1–2): Foundation

**Tasks**
- [ ] New Laravel 13 project, Git repo, staging server, CI (GitHub Actions: tests + Pint + PHPStan)
- [ ] Inertia + React + Tailwind (RTL ready), Vite PWA plugin installed
- [ ] Locales: `ps`, `prs` (Dari), `en`; middleware sets locale + `dir`
- [ ] Phone OTP login (+ email fallback), nickname, privacy defaults
- [ ] Roles & permissions (spatie): admin, student, mentor, moderator, instructor, reviewer, agent
- [ ] Filament admin panel with 2FA for admins
- [ ] Base layout: bottom nav, header with language + data-saver toggles

**Done when:** a user can sign up with a phone number on a phone, choose Dari, see an RTL home screen; an admin can log in to `/admin` with 2FA.

**Build prompt**
```
Read CLAUDE.md. Set up the foundation for the AI Institute LMS:
1) Install Inertia (React), Tailwind with RTL support using logical properties, and vite-plugin-pwa.
2) Add locales ps, prs, en with a SetLocale middleware that also sets <html dir> (rtl for ps/prs).
3) Build phone-number OTP authentication: otp_codes table, hashed codes, 5-minute expiry,
   max 5 attempts, per-phone and per-IP rate limiting, an SmsSender interface with a Log driver
   for local and a placeholder HTTP driver. Email OTP as fallback.
4) Users table per docs/06-database-schema.md (Identity section).
5) Install spatie/laravel-permission and seed the roles listed in docs/04.
6) Install Filament at /admin, restricted to admin role, with 2FA.
7) Create the mobile app shell layout: bottom navigation (Home, Courses, My Learning, Downloads,
   Profile), top bar with language switcher and data-saver toggle.
Write feature tests for OTP login, rate limiting and locale switching.
```

---

## Sprint 2 (weeks 3–4): Catalog and learning

**Tasks**
- [ ] Models + migrations: levels, tracks, categories, courses, sections, lessons, enrollments, lesson_progress
- [ ] Filament resources to manage them (with sort/drag)
- [ ] Bunny Stream: direct upload from admin, store video ID, webhook for "video ready"
- [ ] Lesson player: hls.js, quality selector, audio-only mode, speed, resume position
- [ ] Free enrollment, progress tracking, "Continue learning"
- [ ] Catalog + course page + lesson page (mobile-first)

**Done when:** you upload a Level 0 lesson in Pashto, a student enrolls, watches at 360p, leaves, and resumes at the same second.

**Build prompt**
```
Read CLAUDE.md and docs/04, 06, 07. Implement the Catalog and Learning modules:
migrations/models for levels, tracks, categories, courses (language + translation_group_id),
course_sections, lessons, enrollments, lesson_progress. Filament resources with reordering.
Bunny Stream integration: a BunnyStreamService for creating videos, TUS direct upload from Filament,
signed playback URLs (expiring) for non-preview lessons, and a webhook endpoint that marks videos ready.
Student pages in Inertia/React: Catalog (level chips, filters), Course page (sticky Enroll button),
Lesson player (hls.js, 240/360/480/720 selector defaulting to 360 or 240 in data-saver mode,
audio-only toggle, playback speed, resume from last_position saved every 10 seconds via API).
Free enrollment flow and a Continue Learning card on Home. Add feature tests.
```

---

## Sprint 3 (weeks 5–6): Quizzes, design system, PWA

**Tasks**
- [ ] Question banks, quizzes, attempts (lesson quizzes, final exam, placement test)
- [ ] Design system components (file 07) and polish all screens
- [ ] PWA: manifest, service worker, install prompt, offline banner
- [ ] Lesson text with copy-paste prompt boxes
- [ ] Performance pass: Lighthouse mobile 90+

**Done when:** a student completes a quiz on a phone, sees explanations, installs the app to the home screen, and the shell loads offline.

**Build prompt**
```
Implement the Assessment module per docs/06: question_banks, questions, quizzes, quiz_attempts.
Quiz UI: one question per screen, large options, instant feedback with explanation for lesson quizzes;
final exams draw N random questions from the bank, use a timer and max attempts, show results only after submit.
Placement quiz that suggests a level. Then build the design-system components listed in docs/07
as React components with RTL support, and refactor existing pages to use them.
Configure vite-plugin-pwa: manifest (icons, standalone, theme), app-shell caching, network-first API,
an install prompt shown after the first completed lesson, and an offline banner.
```

---

## Sprint 4 (weeks 7–8): Payments and commerce (HesabPay)

**Tasks**
- [ ] Orders, order_items, payments, gateway_events, coupons, vouchers, scholarships
- [ ] `PaymentGateway` interface; `HesabPayGateway` driver (file 09)
- [ ] Checkout screen; success/failure pages; webhook handler; idempotency
- [ ] Voucher batches for agents (printable codes, PDF)
- [ ] Admin: orders, refunds, reconciliation export (CSV)

**Done when:** in the HesabPay sandbox, a student buys a course, the webhook marks the order paid, and the enrollment appears, even if the student closes the browser before returning.

**Build prompt**
```
Implement the Commerce and Payments modules per docs/06 and docs/09.
Create a PaymentGateway contract (createCheckout(Order): CheckoutSession, handleWebhook(Request): void,
refund(Payment, int $amount): void) and a HesabPayGateway driver that creates a hosted payment session
via the HesabPay API with the API key from config, redirects the user to the returned payment URL,
and processes webhooks after verifying them as described in HesabPay's docs (keep the verification
in one class so it is easy to adjust to their current API). Store every webhook in gateway_events with a
unique event id so it is processed once. OrderPaid event -> create enrollments.
Add coupons (percent/fixed, scholarship flag), voucher batches with printable PDF codes for agents,
and Filament resources for orders, payments, refunds, coupons and vouchers. Feature tests with a fake gateway.
```

---

## Sprint 5 (weeks 9–10): Certificates and exams

**Tasks**
- [ ] Certificate templates (Pashto, Dari, English), mPDF rendering with correct shaping
- [ ] Auto-issue on `CourseCompleted` + `ExamPassed` rules
- [ ] Public verification page `/verify/{code}` + QR code
- [ ] Share: link, PDF, "Add to LinkedIn" URL
- [ ] Admin revoke / reissue with reason
- [ ] Assignments + mentor grading (for Level 2+)

**Done when:** a student passes a final exam and receives a PDF whose QR code opens a verification page showing "Valid".

**Build prompt**
```
Implement the Certification module per docs/10. certificate_templates and certificates tables.
CertificateIssuer service: issues when completion rules are met (all required lessons completed AND,
for achievement certificates, final exam passed). Unique code format PREFIX-YEAR-6 chars (no ambiguous characters),
verification_hash. Render the PDF with mPDF using a Pashto/Dari-capable font (Vazirmatn or Noto Naskh Arabic),
RTL layout for ps/prs, LTR for en, QR code linking to /verify/{code}. Public verify page (no login) showing name,
course, date, type and status. Share link and an "Add to LinkedIn profile" link. Admin revoke with reason.
Also implement assignments and submissions with mentor grading in Filament.
```

---

## Sprint 6 (weeks 11–12): Telegram, notifications, offline — BETA LAUNCH

**Tasks**
- [ ] Telegram bot: link account, daily lesson reminder, quiz in chat, notifications (file 16)
- [ ] Notifications: in-app, web push, Telegram, SMS for payments only
- [ ] Offline downloads in the PWA (audio + low-res video), offline progress queue
- [ ] Analytics events (sign-up, lesson start/complete, purchase)
- [ ] Load test; backup + restore test; error monitoring

**Done when:** a beta student downloads a lesson, switches on airplane mode, watches it, and their progress syncs when they reconnect.

---

## Sprint 7 (weeks 13–14): Android app

- [ ] Capacitor wrapper around the PWA (file 11), native file storage for downloads, push via FCM
- [ ] Play Store listing in Pashto, Dari and English
- [ ] Deep links (`/course/...`, `/verify/...`)

**Done when:** the app is in the Play Store internal testing track, and downloads survive closing the app.

---

## Sprint 8 (weeks 15–16): AI tutor + dashboards — PAID LAUNCH

**Tasks**
- [ ] AI tutor: answers only from course content (retrieval over lesson text + glossary), in the student's language, with a daily message limit per plan
- [ ] Membership plans (monthly) through HesabPay
- [ ] Admin dashboard: active learners, completion per course, revenue by gateway, top drop-off lessons
- [ ] Ratings and reviews

**Build prompt (AI tutor)**
```
Implement the AiTutor module. Index lesson body text, prompts and the glossary per course into chunks
(store embeddings with a vector column or use Meilisearch hybrid search). When a student asks a question in a lesson,
retrieve the top chunks from that course and call the LLM through a TutorClient interface with a system prompt:
answer in the student's locale (Pashto or Dari), simply, using only the provided course material, and say so
when the answer is not in the course. Limit messages per day by plan; log tokens; let students rate answers.
```

---

## After sprint 8 (months 5–12)

| Month | Build |
|---|---|
| 5–6 | Stripe Checkout (US LLC) for students abroad + "Sponsor a learner"; organizations (B2B seats) |
| 6–8 | Marketplace: instructor application, course builder for instructors, review queue, earnings, payouts |
| 8–10 | Programs/diplomas with cohorts and deadlines; iOS app; gamification (points, streaks, badges) |
| 10–12 | Open Badges export, job/freelance board, public instructor profiles, performance and scaling |
