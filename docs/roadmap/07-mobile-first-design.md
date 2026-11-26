# 07 — Mobile-first design (app-like, RTL)

Design for **a cheap Android phone, 360×740 px screen, on 3G**. If it works there, it works everywhere. Desktop is a wider version of the mobile layout, not a separate design.

## Design principles

1. **Bottom navigation like an app** with 4–5 tabs: Home · Courses · My Learning · Downloads · Profile.
2. **One main action per screen** (e.g. a big "Continue lesson" button).
3. **Thumb-friendly:** tap targets 48 px or more, main buttons near the bottom.
4. **RTL first:** Pashto/Dari layouts mirror (icons, progress bars, arrows). Use Tailwind logical properties (`ms-`, `me-`, `ps-`, `pe-`, `start-`, `end-`) and `dir="rtl"` on `<html>`.
5. **Readable script:** body text 16–17 px, line-height 1.8 for Arabic script, no thin font weights.
6. **Data-light:** no auto-playing video, WebP images, skeleton loaders instead of spinners, a "Data saver" toggle in the header.
7. **Works offline:** a clear "Downloaded ✓" state on lessons; an offline banner when there is no connection.
8. **Calm, trustworthy look:** plenty of white space, one brand color, real progress indicators. No clutter.

## Screens (student app)

| # | Screen | Key elements |
|---|---|---|
| 1 | Splash / language choice | پښتو · دری · English, big buttons |
| 2 | Welcome / onboarding (3 slides) | What you'll learn · Certificates · Works offline |
| 3 | Sign up / log in | Phone number field with +93 default, OTP screen, "Continue with Telegram/Google" |
| 4 | Placement quiz | 8 quick questions → suggested level |
| 5 | Home | Continue-learning card, your level progress, "New this week in AI", recommended courses, free courses row |
| 6 | Catalog | Level chips (0–5), track chips, search, filter sheet |
| 7 | Course page | Trailer (tap to play), outcomes, syllabus accordion, certificate preview, price + sticky "Enroll" button |
| 8 | Lesson player | Video/audio toggle, quality selector (240/360/480/720), speed, prompts box with copy buttons, "Mark complete", next lesson, download icon |
| 9 | Quiz | One question per screen, big options, instant feedback with explanation |
| 10 | Exam | Timer, progress, review before submit |
| 11 | Assignment | Instructions, upload/photo/link, status |
| 12 | My Learning | In progress / completed tabs, certificates |
| 13 | Certificate | Preview, download PDF, share link, "Add to LinkedIn" |
| 14 | Downloads | Storage used, delete, download all for course |
| 15 | Checkout | Price, coupon, pay with HesabPay / card (Stripe for abroad) / voucher code |
| 16 | AI tutor | Chat in selected language, suggested questions from the lesson |
| 17 | Profile & settings | Nickname, language, data saver, privacy, notifications, logout |
| 18 | Certificate verify (public) | Enter code or scan QR → valid/revoked + name, course, date |

## Design system (build in Sprint 3)

- **Colors:** `primary-600` (brand), `accent-500`, `success`, `warning`, `danger`, gray scale. Must pass WCAG AA contrast.
- **Type scale:** 12 / 14 / 16 / 18 / 22 / 28 px.
- **Components:** Button (primary, secondary, ghost), Card (course card, continue card), Chip, Bottom sheet, Tabs, Progress bar/ring, Lesson row (with status icons: locked, playing, done, downloaded), Toast, Empty state, Skeleton, Modal, OTP input, Price tag, Badge.
- **Icons:** one icon set (Lucide or Heroicons); mirror direction-sensitive icons in RTL.
- **Illustrations:** your mascot in empty states and onboarding.

## PWA requirements

- [ ] `manifest.webmanifest` with name, short_name, icons (192, 512, maskable), `display: standalone`, theme color, `dir` and `lang`
- [ ] Service worker (vite-plugin-pwa / Workbox): cache the app shell, fonts and images; network-first for API calls
- [ ] "Install app" prompt after the second visit or after the first completed lesson
- [ ] Offline lesson storage: audio + 240p/360p video files stored with the Cache API or IndexedDB; progress queued and synced later
- [ ] Lighthouse PWA + performance score of 90+ on a mobile profile

## Tools to design it

- Sketch the screens in Figma (free plan) or Penpot (free, open source), or have Claude generate the screens as a design mock-up first, then code them.
- Use a ready Tailwind UI kit as a base and adapt it to RTL rather than designing from zero.
