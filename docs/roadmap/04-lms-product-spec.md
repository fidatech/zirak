# 04 — LMS product specification

**Goal:** one platform that is an institute (your courses), a marketplace (other instructors' courses) and a program school (diplomas), with verifiable certificates. It must feel like a mobile app.

Legend: **MVP** = needed for beta launch (week 12) · **P2** = paid launch (week 16) · **P3** = months 5–8 · **P4** = months 8–12

## User roles

| Role | Can do |
|---|---|
| Guest | Browse catalog, watch preview lessons, verify a certificate |
| Student | Enroll, learn, take quizzes/exams, download, earn certificates, pay |
| Instructor (P3) | Create courses, upload lessons, see sales and payouts, answer questions |
| Reviewer (P3) | Review instructor courses before publishing; grade projects |
| Mentor (P2) | Grade projects, run study circles, answer questions |
| Moderator | Moderate comments and groups; female moderators for women-only spaces |
| Organization admin (P3) | B2B: buy seats, assign courses to staff, see reports |
| Agent (P2) | Register students, sell vouchers, earn commission |
| Admin | Everything |

## Student features

| Feature | Priority | Notes |
|---|---|---|
| Sign up / log in with phone number + OTP (SMS) or email; Google login optional | MVP | Phone number first; many users have no email. Nicknames allowed |
| Choose language: Pashto / Dari / English UI | MVP | Changes the interface AND the default course language |
| Placement quiz ("Where should I start?") | P2 | Suggests a level |
| Catalog: levels, industry tracks, search, filters (language, level, free/paid, duration) | MVP | |
| Course page: outcomes, syllabus, preview, instructor, reviews, price, certificate type | MVP | |
| Enroll free / buy / redeem voucher or coupon | MVP (free) / P2 (paid) | |
| Lesson player: video (adaptive quality, 240p–720p), audio-only mode, speed control, resume | MVP | Data saver ON by default |
| Lesson text + copy-paste prompts + downloadable PDF | MVP | |
| Mark complete, progress bar, "continue where you left off" | MVP | |
| Quizzes after lessons (auto-graded) | MVP | |
| Assignments / projects: upload file, link or text; mentor feedback | P2 | |
| Final exam with question bank, timer, attempts limit | MVP | |
| Certificates: auto-issued, PDF + share link + QR | MVP | |
| Offline download (PWA cache, later the app) | MVP (PWA) / P2 (app) | |
| Q&A per lesson, comments | P2 | Moderated |
| Study circles (groups) | P2 | Can link to Telegram groups |
| AI tutor chat in Pashto/Dari, answers from course content | P2 | Rate limited |
| Notifications: in-app, push, Telegram, SMS for key events | P2 | |
| Streaks, points, badges, leaderboards (opt-in, nickname only) | P3 | Motivation |
| Wishlist, cart, order history, invoices | P2 | |
| Profile: nickname, language, privacy options, "hide me from leaderboards" | MVP | Default private |
| Learning paths / programs (diplomas) with cohorts and deadlines | P4 | |
| Job/freelance board | P4 | |

## Instructor features (P3, marketplace)

| Feature | Notes |
|---|---|
| Application form + sample lesson | Vetting (file 12) |
| Course builder: sections, lessons (video/text/quiz/assignment), drag to reorder | |
| Video upload directly to Bunny Stream (resumable) | |
| Pricing, coupons, free preview lessons | Within platform price rules |
| Submit for review → reviewer approves / requests changes | Nothing goes live without review |
| Sales, students, ratings, completion analytics | |
| Earnings and payout requests (HesabPay / bank / Stripe Connect for abroad) | |
| Answer Q&A | |

## Admin features

| Feature | Priority |
|---|---|
| Admin panel (Filament): users, courses, lessons, orders, certificates, settings | MVP |
| Course & lesson management with translations | MVP |
| Coupons, vouchers (printable codes for agents), scholarships (100% coupons with reason) | P2 |
| Orders, payments, refunds, reconciliation with HesabPay/Stripe dashboards | P2 |
| Certificate management: issue manually, revoke, re-issue | MVP |
| Reports: sign-ups, active learners, completion per course, revenue per channel | P2 |
| Content moderation queue | P2 |
| Agent management + commissions | P2 |
| Instructor review queue + payouts | P3 |
| Organization (B2B) accounts | P3 |
| Feature flags + maintenance mode | MVP |

## Public pages

| Page | Priority |
|---|---|
| Home (app-like), catalog, course page, level pages, track pages | MVP |
| Certificate verification `/verify/{code}` | MVP |
| About, FAQ, contact (WhatsApp/Telegram links), terms, privacy, refunds | MVP |
| Blog in Pashto/Dari (SEO) | P2 |
| "Become an instructor", "For organizations", "Sponsor a learner" | P3 |

## Non-functional requirements

- **Speed:** first screen under 3 seconds on 3G; total JS for the student app under 200 KB gzipped where possible.
- **Data saving:** images WebP/AVIF, lazy loading, videos default to 360p, audio-only option.
- **RTL:** full right-to-left layout for Pashto/Dari; LTR for English and code blocks.
- **Offline:** downloaded lessons play without internet; progress syncs when online again.
- **Privacy & safety:** minimal data, nicknames allowed, no public student lists, women-only groups.
- **Security:** OTP rate limits, 2FA for admins/instructors, signed video URLs, webhook signature checks.
- **Accessibility:** large tap targets (48 px), readable font sizes (16 px+), subtitles on all videos.
