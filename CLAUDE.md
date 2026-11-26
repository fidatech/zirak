# CLAUDE.md — Zirak LMS

Online AI & skills institute for Afghan learners in **Dari (`prs`)**, **Pashto (`ps`)** and **English (`en`)**. Most users are on cheap Android phones with slow, expensive mobile data. Production hosting is **cPanel** (Apache, PHP 8.3+, MySQL, cron) — no Node, Redis or long-running workers on the server.

## Stack
- Laravel 13 (PHP 8.3+), Blade views + Tailwind CSS 4 + Alpine.js, built with Vite. **Built assets in `public/build` are committed** (cPanel has no Node).
- Filament 5 admin panel at `/admin` (`app/Filament`). Only users with role `admin` can enter.
- Database queue + cache + sessions (`database` drivers). Cron runs `schedule:run` and `queue:work --stop-when-empty`.
- Payments: `App\Services\Payments` — `CheckoutService` picks `HesabPayGateway` (sandbox/live) or `FakeGateway` (local only; refused in production).
- SMS: `App\Services\Sms\SmsSender` (only `LogSmsSender` so far).
- Video: Bunny Stream embeds by `lessons.video_id` + `BUNNY_LIBRARY_ID`.
- AI tutor: `App\Services\Tutor` — `TutorService` + `CourseContext` (keyword-ranked lesson text, no vector DB) + `AnthropicTutorClient` (Claude Messages API) or `FakeTutorClient` (off in production). Messages in `tutor_messages` (roles student | ai | teacher); escalated questions are answered in Admin → Student questions.
- Telegram: `App\Services\Telegram` — `TelegramClient` (Bot API), `TelegramAuthService` (deep-link login/link: one-time token kept in the browser session, confirmed with inline buttons by the same Telegram user), `TelegramBot` (webhook updates; `/webhooks/telegram` requires the `X-Telegram-Bot-Api-Secret-Token` header). Commands: `zirak:telegram-setup`, `zirak:telegram-reminders` (scheduled daily 15:30 Kabul).

The roadmap in `docs/roadmap` mentions Inertia/React; we chose Blade + Alpine instead because it needs no build step or Node on cPanel and stays light on slow phones.

## Domain model
`Category` → `Course` (price_afn, price_usd_cents, is_free, language, level, status, certificate_type) → `Chapter` → `Lesson` (Learn) and `Question` (kind `practice` | `test`).
Progress: `Enrollment`, `LessonCompletion`, `PracticeAnswer`, `TestAttempt`. Commerce: `Order`, `Coupon`, `GatewayEvent`. `Certificate` (code `ZRK-YY-XXXXXX`, public `/verify/{code}`).
`ProgressService` computes chapter/course state (Learn → Practice → Test; test unlocks after lessons + practice; pass mark `config('zirak.pass_mark')`). `CertificateIssuer` issues once the course is complete.

## Rules
1. **Mobile first, RTL first.** Use logical Tailwind classes (`ms-/me-/ps-/pe-/start-/end-`), never `ml-/mr-/left-/right-` for direction-dependent spacing. Check every page in `?lang=prs`, `?lang=ps` and `?lang=en`.
2. **All UI text through `__('app.*')`** in `lang/{prs,ps,en}/app.php`; the three files must keep the same keys. Show numbers with `n()`, percentages with `pct()`, AFN with `afn()`.
3. **Low data:** no autoplay, no heavy JS libraries, keep `public/build` small.
4. **Money** is integers (AFN, or US cents). Access is granted only by `EnrollmentService::markPaid()` from a **verified** webhook, the demo gateway, a 100% coupon, or an admin "mark paid" — never from the browser return URL.
5. **Webhooks** are stored in `gateway_events`; processing must stay idempotent (`markPaid` locks the order row).
6. **Privacy:** phone numbers are never shown to other students or instructors; nicknames allowed.
7. Practice answers return the correct option only **after** the student answers. The tutor must not hand out test answers, and course material goes into the prompt as reference data inside `<course_material>`.
8. Any new student-facing feature needs a feature test. Run `php artisan test` and `vendor/bin/pint` before committing. Rebuild assets with `npm run build` when views or CSS change, and commit `public/build`.

## Commands
```
php artisan migrate --seed              # local demo data
php artisan zirak:admin email --password=...
php artisan test
vendor/bin/pint
npm run build
```
