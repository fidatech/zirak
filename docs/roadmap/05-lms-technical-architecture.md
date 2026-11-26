# 05 — LMS technical architecture (Laravel)

## Stack decision

| Layer | Choice | Why |
|---|---|---|
| Framework | **Laravel 13** (PHP 8.3+) | Current major release; it requires PHP 8.3 and adds a first-party Laravel AI SDK, useful for the AI tutor |
| Student frontend | **Inertia.js + React + Tailwind CSS**, built as a **PWA** | App-like feel, page transitions, offline support; the same React components can later run inside the Capacitor app |
| Alternative frontend | Livewire 3 + Alpine + Tailwind | Choose this only if you prefer staying in Blade. It is harder to make fully offline |
| Admin panel | **Filament** (latest) | Fast CRUD for admin, reviewers and later instructors |
| API | Laravel **Sanctum** + `/api/v1` JSON routes | Used by the mobile app and the Telegram bot |
| Database | MySQL 8.4 or PostgreSQL 16 | Pick the one you already run (e.g. same as Tabib / Realist ERP) |
| Cache / queue | Redis + **Laravel Horizon** | Video processing callbacks, emails, certificates, notifications |
| Search | Laravel Scout + Meilisearch | Fast search in Pashto/Dari |
| Video | **Bunny Stream** (HLS, adaptive bitrate, token-signed URLs) | Cheap, fast in Asia, protects paid videos |
| Files | Bunny Storage or S3-compatible (Cloudflare R2) | PDFs, images, downloads |
| PDF certificates | **mPDF** (good Arabic-script / RTL shaping) or Browsershot (Chromium) | dompdf does not shape Pashto/Dari well |
| Payments | HesabPay hosted checkout (AFN); **Stripe Checkout** via the US LLC (USD); later **Stripe Connect** for instructor payouts abroad | File 09 |
| SMS / OTP | An SMS provider that delivers to Afghan networks (test Twilio, Vonage and a local aggregator); fall back to Telegram login / email OTP | Test delivery to Roshan, AWCC, MTN, Etisalat (e&) and Salaam numbers |
| Telegram | `nutgram/laravel` or `irazasyed/telegram-bot-sdk` | Bot + channel posting |
| AI tutor | Laravel AI SDK or the Anthropic/OpenAI PHP SDKs + simple RAG over lesson text | File 08, sprint 8 |
| Monitoring | Laravel Nightwatch or Sentry; Laravel Pulse | |
| Hosting | VPS (Hetzner/DigitalOcean) via Laravel Forge/Ploi, or Laravel Cloud | Start with 1 app server + managed DB |
| CDN / security | Cloudflare in front of the site | Caching, DDoS protection, fast static files |
| Mobile app | **Capacitor** wrapper of the PWA first (fast), native features via plugins | File 11 |

## Recommended packages

```
laravel/sanctum            laravel/horizon           laravel/scout
laravel/socialite          laravel/cashier (Stripe)  inertiajs/inertia-laravel
filament/filament          spatie/laravel-permission spatie/laravel-medialibrary
spatie/laravel-translatable spatie/laravel-sluggable spatie/laravel-activitylog
spatie/laravel-backup      spatie/laravel-sitemap    spatie/laravel-query-builder
mpdf/mpdf (or carlos-meneses/laravel-mpdf)            simplesoftwareio/simple-qrcode
nutgram/laravel            meilisearch/meilisearch-php
```
Frontend: `react`, `@inertiajs/react`, `tailwindcss`, `vite-plugin-pwa` (Workbox), `hls.js` (video), `idb-keyval` (offline progress queue).

## Application modules (folders under `app/Domain/` or modules)

```
Identity        users, phone OTP, roles, profiles, language preference
Catalog         categories, levels, tracks, courses, sections, lessons, translations, media
Learning        enrollments, lesson progress, notes, downloads, study circles
Assessment      quizzes, question banks, attempts, exams, assignments, grading
Certification   certificate templates, issuing, verification, revocation
Commerce        products/prices, carts, orders, payments, coupons, vouchers, refunds, scholarships
Payments        gateways: HesabPay, Stripe (one interface, two drivers)
Marketplace     instructors, applications, course reviews, revenue share, payouts
Organizations   B2B accounts, seats, assignments, reports
Engagement      notifications, points, badges, streaks, Q&A, comments
AiTutor         conversations, retrieval over course content, limits
Integrations    Telegram bot, YouTube links, analytics
Admin           Filament panels
```

## Key design rules

1. **Multilingual content model:** a course has a `language` (ps / fa-AF / en). Pashto and Dari versions are **separate courses** linked by a `translation_group_id`, not one course with translated fields. Lesson text, videos and quizzes differ per language. UI strings use Laravel's `lang/ps`, `lang/prs` (Dari), `lang/en` files.
2. **Payment gateway interface:** `PaymentGateway` contract with `createCheckout(Order)`, `handleWebhook(Request)`, `refund(Payment)`. Drivers: `HesabPayGateway`, `StripeGateway`. The order flow never talks to a gateway directly.
3. **Everything that matters is an event:** `LessonCompleted`, `CourseCompleted`, `ExamPassed`, `OrderPaid` → listeners issue certificates, send notifications and update stats (queued).
4. **Video never goes through your server:** upload directly from the browser to Bunny (TUS), store only the video ID; play with signed, expiring URLs for paid lessons.
5. **API-first for the app:** every student action the web does also exists in `/api/v1` with Sanctum tokens, so the mobile app and Telegram bot reuse it.
6. **Money in minor units:** store `amount` as integer (AFN or cents) + `currency`. Never floats.
7. **Idempotent webhooks:** store each gateway event ID; process it once.
8. **Soft deletes + activity log** on courses, orders and certificates.

## Environments

| Env | Purpose |
|---|---|
| local | Development (Laravel Herd or Sail) |
| staging | `staging.yourdomain.com`, HesabPay sandbox, Stripe test mode |
| production | Live keys, daily backups to a second provider |

## Security checklist
- [ ] OTP: 5 attempts max, 60-second resend, per-IP and per-phone rate limits
- [ ] 2FA for admin, instructor, reviewer accounts
- [ ] Signed URLs for paid video and downloads
- [ ] Webhook signature verification (HesabPay, Stripe)
- [ ] Policies (Laravel Gates) on every model action
- [ ] CSP headers, HTTPS only, secure cookies
- [ ] Daily DB backup + weekly restore test
- [ ] No student phone numbers visible to instructors or other students
