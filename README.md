# Zirak · زیرک · ځیرک

Online AI & skills institute for Afghanistan in **Dari and Pashto** (and English): pay-per-course catalog, **Learn → Practice → Test** chapters, progress reports and verifiable certificates. Mobile-first and installable as an app (PWA).

Built with Laravel 13, Blade + Tailwind CSS 4 + Alpine.js, and Filament 5 for the admin panel. Designed to run on ordinary **cPanel** hosting (MySQL, no Node or Redis needed on the server).

## What works today

| Area | Features |
|---|---|
| Students | Phone-number login with a one-time code · Dari / Pashto / English with right-to-left layout · subjects and course catalog with search and filters · course pages with price and buy button · chapter map with Learn, Practice, Test · lessons with video (Bunny Stream), text, copyable prompt and "your turn" task · practice questions with instant feedback · scored chapter tests (pass mark 70%) · weekly progress report · certificates with a public verification page · installable app (PWA) with an offline page |
| Payments | Buy each course separately in AFN through **HesabPay** hosted checkout · webhooks verified with HesabPay and processed once · coupons and 100% scholarship codes · a demo checkout for local testing |
| Admin (`/admin`) | Categories (Dari/Pashto/English names, colour, icon) · courses with prices, level, language, certificate type and publishing · chapters with lessons and practice/test questions · orders with "mark paid" for manual payments · coupons · certificates (revoke/restore) · users and roles · dashboard numbers |

Not built yet (next sprints, see `docs/roadmap/08-lms-build-sprints.md`): real SMS provider, Stripe for students abroad, AI tutor, Telegram bot, offline lesson downloads, Android app, instructor marketplace.

## Run it on your computer

Requirements: PHP 8.3+, Composer, Node 20+.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed          # demo subjects, courses and coupons
php artisan zirak:admin you@example.com --password=secret123
npm install && npm run build        # or: npm run dev
php artisan serve
```

- Open http://localhost:8000 and log in with any Afghan number, e.g. `0790000000`. With `SMS_DRIVER=log` the 6-digit code is written to `storage/logs/laravel.log`.
- Admin panel: http://localhost:8000/admin (the email and password from `zirak:admin`).
- Paid courses use a demo checkout page while `HESABPAY_DRIVER=fake`.
- Demo coupons: `WELCOME30` (30% off) and `SCHOLARSHIP` (free).

Run the tests with `php artisan test`.

## Deploy to cPanel

Step by step: [`docs/DEPLOY-CPANEL.md`](docs/DEPLOY-CPANEL.md).

## Project documents

- `CLAUDE.md` — rules for anyone (or Claude Code) working on the code
- `docs/roadmap/` — the full business and build roadmap, course production kit and templates

## Before launch

- All Pashto and Dari text (`lang/ps`, `lang/prs`, demo content) is a draft. Have native editors review it.
- Choose an SMS provider that delivers to Afghan networks and add a driver (see `app/Services/Sms`).
- Get HesabPay merchant approval, test with `HESABPAY_DRIVER=sandbox`, then switch to `live`.
