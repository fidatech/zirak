# 11 — Mobile app plan

Get into the app store fast, without building a second product. The plan has three steps.

## Step 1 — PWA (by week 6)
The website installs on the home screen and works like an app: no address bar, offline shell, push notifications on Android. Many users will never need more than this.

## Step 2 — Android app with Capacitor (weeks 13–14)
Capacitor wraps the same React/Inertia frontend into a real Android app. It adds what the PWA lacks:

| Need | Capacitor plugin / approach |
|---|---|
| Reliable large offline downloads | `@capacitor/filesystem` (store video/audio files in app storage) |
| Push notifications | `@capacitor/push-notifications` + Firebase Cloud Messaging |
| Deep links (`/course/x`, `/verify/code`) | Android App Links |
| Network status | `@capacitor/network` (switch to offline mode) |
| Share certificate | `@capacitor/share` |
| Secure token storage | Secure storage plugin; Sanctum token auth via `/api/v1` |

**Architecture note:** inside the app, pages talk to the `/api/v1` JSON API with Sanctum tokens (not session cookies). Build those API endpoints from Sprint 2 onward so the app is ready when you reach it.

**Play Store checklist**
- [ ] Google Play developer account (one-time fee)
- [ ] App name, short + full description in Pashto, Dari, English
- [ ] Screenshots (phone), feature graphic, privacy policy URL
- [ ] Content rating questionnaire; data safety form
- [ ] Payments: selling digital courses inside an Android app can trigger Google Play Billing rules. Before launch, read Google's current Payments policy and decide one of these: (a) the app is for learning only and purchases happen on the website, (b) use Play Billing for in-app purchases, or (c) a program Google allows for your case. Get this right before submitting.
- [ ] Internal test → closed test (20+ testers for the required period if your account type requires it) → production

## Step 3 — iOS (months 8–10)
The same Capacitor project builds for iOS. You need an Apple Developer account and a Mac (or a cloud Mac service). Apple has similar in-app purchase rules for digital content, so review them before submitting.

## Later: fully native (optional, year 2)
Only if the Capacitor app hits real limits (heavy offline video, complex animations), rebuild in Flutter or React Native against the same `/api/v1`. Because the API already exists, this is a frontend-only project.

## Also consider
- **NativePHP for mobile** is a Laravel-native way to ship mobile apps. Evaluate it against Capacitor when you reach Step 2; pick whichever your stack supports better at that time.
- **Telegram Mini App:** the same frontend can open inside Telegram as a Mini App. This is good for users who live in Telegram and do not want another app.
