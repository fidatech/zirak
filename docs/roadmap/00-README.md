# AI Institute Afghanistan — Complete Build Roadmap

A step-by-step pack for building an online AI institute, course marketplace and certificate platform in **Pashto and Dari**, from zero to a mobile app.

Read the files in order. Each file is one stage of the work and ends with a checklist.

| # | File | What it covers | When |
|---|------|----------------|------|
| 01 | `01-strategy-how-it-works.md` | Why students will pick you over free YouTube videos; the business model | Read first |
| 02 | `02-master-roadmap.md` | All phases, week by week, from setup to university-style programs | Read first |
| 03 | `03-business-setup.md` | Brand, domains, HesabPay merchant account, US LLC and Stripe, legal pages, team | Weeks 1–2 |
| 04 | `04-lms-product-spec.md` | Every feature of the LMS, by user role, marked MVP / later | Before coding |
| 05 | `05-lms-technical-architecture.md` | Laravel 13 stack, packages, modules, hosting, video, security | Before coding |
| 06 | `06-database-schema.md` | All tables and key columns | Before coding |
| 07 | `07-mobile-first-design.md` | App-like design, RTL Pashto/Dari, screens, PWA rules | Before coding |
| 08 | `08-lms-build-sprints.md` | 8 two-week sprints, each with tasks and ready-to-use build prompts | Weeks 1–16 |
| 09 | `09-payments-hesabpay-stripe.md` | HesabPay checkout + webhooks, Stripe via the US LLC, payouts | Sprint 4 |
| 10 | `10-certificates-and-exams.md` | Certificate types, verification, exam rules, legal naming | Sprint 5 |
| 11 | `11-mobile-app-plan.md` | PWA → Android app → iOS, offline downloads | Sprints 6–8 |
| 12 | `12-marketplace-instructors.md` | Opening the platform to other teachers: vetting, revenue share, quality | Month 6+ |
| 13 | `13-course-production-kit.md` | Everything needed to make courses: outlines, scripts, recording, books | Start week 2 |
| 14 | `14-first-courses-build-order.md` | Exactly which courses to make first, and in what order | Start week 2 |
| 15 | `15-youtube-channel.md` | YouTube setup, show formats, upload routine | Week 3 |
| 16 | `16-telegram-bot-and-channel.md` | Telegram channel, groups and bot | Week 3 |
| 17 | `17-facebook-page-and-group.md` | Facebook page, group, ads | Week 3 |
| 18 | `18-launch-checklist.md` | Beta launch and paid launch checklists | Weeks 10–16 |
| 19 | `19-kpis-and-operations.md` | Numbers to track weekly, team roles, routines | Ongoing |
| — | `CLAUDE.md` | Drop into the root of the Laravel repo, so Claude Code follows the project rules | Sprint 1 |
| — | `templates/` | Course outline, lesson script, quiz CSV, content calendar, glossary, instructor agreement outline, certificate layout | As needed |

## The one-paragraph plan

Build a Laravel 13 LMS that looks and feels like a mobile app (a PWA first, then the same app in the Play Store). Launch with your own institute courses only: Levels 0–1 free, Level 2 paid through HesabPay. Use YouTube, Telegram and Facebook as free teaching channels that feed the LMS. After 6 months, open the marketplace to vetted instructors, add Stripe (through your US LLC) for students abroad, and group courses into diploma programs. Certificates are verifiable online with a QR code.

## Important notes

- Pricing, legal and payment details were checked in November 2026. Re-check them before you launch, because they change.
- All Pashto and Dari text in the templates is a **draft** and must be checked by native editors.
- This is not legal or financial advice. Have a lawyer or accountant review the LLC, Stripe, tax and certificate items before launch.
